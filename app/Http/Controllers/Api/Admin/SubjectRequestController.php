<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\User\SubjectRequestStatus;
use App\Models\CategoryLevelFour;
use App\Models\SubjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SubjectRequestController extends Controller
{
    //

    public function index(Request $request) {
		$subjects = SubjectRequest::query();

		if (!empty($request->search)) {
			$subjects = $subjects->where('subject','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'subject', 'status', 'created_at'])) {
			$subjects = $subjects->orderBy($sortBy, $sortDirection);
		} else {
			$subjects = $subjects->orderBy('id', 'DESC');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

        $subjects->whereIn('status',['Pending','Reject']);

		$paginated = $subjects->with([
			'category',
			'subCategory',
			'subSubCategory',
			'user' => function ($query) {
				return  $query->select('id', 'full_name', 'email');;
			}
		])->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Subjects fetched successfully', [
			'subjects' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);
	}

    public function approve(Request $request, $ID) 
    {
        $request->validate([
            'status' => 'required|string|in:Active,Reject',
        ]);

        $subject = SubjectRequest::find($ID);
        if(!$subject) {
            return jsonResponse(false, 'Subject request not found', null, 404);
        }

        DB::beginTransaction();
        try {
            $subject->status = 'Active';
            $subject->reason = $request->reason;
            $subject->save();

         
            CategoryLevelFour::create([
                'category_id' => $subject->category_id,
                'sub_category_id' => $subject->sub_category_id,
                'sub_sub_category_id' => $subject->sub_sub_category_id,
                'title' => $subject->subject,
                'slug' => generateUniqueSlug($subject->subject,'App\Models\CategoryLevelFour'),
            ]);

            $mailData = [
                'status' => 'Approved',
                'reason' => $request->reason,
                'subject' => $subject->subject
            ];

            Mail::to($subject->user->email)->send(new SubjectRequestStatus($mailData));

            DB::commit();
            return jsonResponse(true, 'Subject request approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'An error occurred while approving the subject request: ' . $e->getMessage(), null, 500);
        }
    }


    public function decline(Request $request, $ID) 
    {
        $request->validate([
            'status' => 'required|string|in:Active,Reject',
            'reason' => 'required|string|max:255',
        ]);

        $subject = SubjectRequest::find($ID);
        if(!$subject) {
            return jsonResponse(false, 'Subject request not found', null, 404);
        }


        DB::beginTransaction();
        try {
            $subject->status = 'Reject';
            $subject->reason = $request->reason;
            $subject->save();
            $mailData = [
                'status' => 'Declined',
                'reason' => $request->reason,
                'subject' => $subject->subject
            ];
            Mail::to($subject->user->email)->send(new SubjectRequestStatus($mailData));
            DB::commit();
            return jsonResponse(true, 'Subject request declined successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'An error occurred while declining the subject request: ' . $e->getMessage(), null, 500);
        }
    }
}