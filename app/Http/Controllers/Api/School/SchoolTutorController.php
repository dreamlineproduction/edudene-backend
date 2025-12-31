<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Mail\School\TutorCreation;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SchoolTutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggedInUser = auth('sanctum')->user();

        $users = User::query()
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_users')
                    ->where('school_id', $loggedInUser->school->id);
            });

        if ($request->filled('search')) {
            $search = $request->search;

            $users->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('tutor', function ($tutor) use ($search) {
                        $tutor->where('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'full_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'full_name', 'email', 'status', 'created_at'])) {
            $users->orderBy($sortBy, $sortDirection);
        } else {
            $users->orderBy('created_at', 'desc');
        }

        $perPage = (int) $request->get('per_page', 10);

        $paginated = $users->with('tutor')->paginate($perPage);

        $users = collect($paginated->items())->map(function ($user) {
            $user->formatted_last_login_datetime = formatDisplayDate($user->last_login_datetime,'j M Y / h:i:s A');

            return $user;
        });

        
        return jsonResponse(true, 'Tutors fetched successfully', [
            'users' => $users,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'full_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:150',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|max:50',
            'phone_number' => 'required|string|min:10|max:15|unique:tutors,phone_number',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'ip_agreement' => 'required|string|in:Yes,No',
            'freelancer' => 'required|string|in:Yes,No',
        ]);


        DB::beginTransaction();
        try {

            // Create user
            $user = User::create([
                'role_id' => 4,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
                'status' => $request->status,
                'timezone' => getDefaultTimezone($request->timezone),
            ]);

            // Generate username
            $userName = generateUniqueSlug(
                $request->full_name,
                User::class,
                $user->id,
                'user_name'
            );

            $user->update([
                'user_name' => $userName,
            ]);

            // Tutor details
            Tutor::create([
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'zip' => $request->zip,
                'is_house' => 'Yes'
            ]);

            // School mapping
            $loggedInUser = auth('sanctum')->user()->load('school');

            $schoolUser =  SchoolUser::create([
                'user_id' => $user->id,
                'school_id' => $loggedInUser->school->id,
                'ip_agreement' => $request->ip_agreement,
                'is_freelancer' => $request->freelancer,
            ]);

            $newPath = $user->id;
            
            // Save 
            if (notEmpty($request->ip_document)) {                            
                $document = finalizeFile($request->ip_document,$newPath);
                $schoolUser->update([
                    'agreement_file' => $document['path'],
                    'agreement_file_url' => $document['url']
                ]);

            }

            ///$schoolInfo = School::where('user_id', $school->id)->first();

            // Mail data
            $mailData = [
                'userName' => $userName,
                'fullName' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password, // temporary password
                'schoolName' => $loggedInUser->school->school_name,
                'loginLink' => env('WEBSITE_URL') . '/school/login',
            ];

            Mail::to($request->email)->send(new TutorCreation($mailData));

            DB::commit();

            return jsonResponse(true, 'Tutor account created successfully.', $user, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $loggedInUser = auth('sanctum')->user()->load('school');

        $user = User::where('id', $id)
            ->whereHas('schoolUser', function ($q) use ($loggedInUser) {
                $q->where('school_id', $loggedInUser->school->id);
            })
            ->with([
                'tutor',
                'schoolUser:*'
            ])
            ->first();

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);
        }

        return jsonResponse(true, 'Tutor details', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $loggedInUser = auth('sanctum')->user();
        $user = User::where('id', $id)
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_users')
                    ->where('school_id', $loggedInUser->id);
            })
            ->with('tutor')
            ->first();
        if (empty($user)) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);            
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
            'phone_number' => 'required|string|min:10|max:15|unique:tutors,phone_number,' . $user->tutor->id,
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'timezone' => getDefaultTimezone($request->timezone),
            'status' => $request->status,
        ]);

        $user->tutor()->update([
            'phone_number' => $request->phone_number,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip' => $request->zip,
        ]);

        return jsonResponse(true, 'Tutor updated successfully.', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $loggedInUser = auth('sanctum')->user();
        $user = User::where('id', $id)
            ->where('role_id', 4)
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_users')
                    ->where('school_id', $loggedInUser->id);
            })
            ->first();
        if (empty($user)) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);
        }
        $user->delete();

        return jsonResponse(true, 'Tutor deleted successfully.');
    }
}
