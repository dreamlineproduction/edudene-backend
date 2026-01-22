<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorPricingController extends Controller
{
    /**
     * Update one-to-one hourly rate
     */
    public function updateOneToOneHourlyRate(Request $request)
    {
        try {
            $request->validate([
                'one_to_one_hourly_rate' => 'required|numeric|min:0',
            ]);

            $user = auth('sanctum')->user();
            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {
                return jsonResponse(false, 'Tutor profile not found', null, 404);
            }

            $tutor->update([
                'one_to_one_hourly_rate' => $request->one_to_one_hourly_rate,
            ]);

            return jsonResponse(true, 'One-to-one hourly rate updated successfully', ['tutor' => $tutor]);
        } catch (\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update trainer hourly rate
     */
    public function updateTrainerHourlyRate(Request $request)
    {
        try {
            $request->validate([
                'trainer_hourly_rate' => 'required|numeric|min:0',
            ]);

            $user = auth('sanctum')->user();
            $tutor = Tutor::where('user_id', $user->id)->first();

            if (!$tutor) {
                return jsonResponse(false, 'Tutor profile not found', null, 404);
            }

            $tutor->update([
                'trainer_hourly_rate' => $request->trainer_hourly_rate,
            ]);

            return jsonResponse(true, 'Trainer hourly rate updated successfully', ['tutor' => $tutor]);
        } catch (\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
