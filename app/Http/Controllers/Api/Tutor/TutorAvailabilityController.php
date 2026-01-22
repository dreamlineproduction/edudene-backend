<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorAvailabilityController extends Controller
{
    /**
     * Enable/Disable one-to-one sessions
     */
    public function updateEnableOneToOne(Request $request)
    {
        try {
            $request->validate([
                'enable_one_to_one' => 'required|boolean',
            ]);

            $user = auth('sanctum')->user();
            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {
                return jsonResponse(false, 'Tutor profile not found', null, 404);
            }

            $tutor->update([
                'enable_one_to_one' => $request->enable_one_to_one,
            ]);

            return jsonResponse(true, 'One-to-one availability updated successfully', ['tutor' => $tutor]);
        } catch (\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Enable/Disable trainer sessions
     */
    public function updateEnableTrainer(Request $request)
    {
        try {
            $request->validate([
                'enable_trainer' => 'required|boolean',
            ]);

            $user = auth('sanctum')->user();
            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {
                return jsonResponse(false, 'Tutor profile not found', null, 404);
            }

            $tutor->update([
                'enable_trainer' => $request->enable_trainer,
            ]);

            return jsonResponse(true, 'Trainer availability updated successfully', ['tutor' => $tutor]);
        } catch (\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Enable/Disable courses
     */
    public function updateEnableCourses(Request $request)
    {
        try {
            $request->validate([
                'enable_courses' => 'required|boolean',
            ]);

            $user = auth('sanctum')->user();
            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {
                return jsonResponse(false, 'Tutor profile not found', null, 404);
            }

            $tutor->update([
                'enable_courses' => $request->enable_courses,
            ]);

            return jsonResponse(true, 'Courses availability updated successfully', ['tutor' => $tutor]);
        } catch (\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
