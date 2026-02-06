<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\OneOnOneClassBooking;
use App\Models\OneOnOneClassSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OneOnOneClassSlotController extends Controller
{
	/**
     *  Fetch slots of a specific tutor grouped by date with pagination
    */ 
	public function index(Request $request) {
        $perPage = $request->get('per_page', 10);
        $tutorId = auth('sanctum')->user()->id;

        // Build base query for slots
        $slotQuery = OneOnOneClassSlot::where('tutor_id', $tutorId);

        if ($request->month) {
            $month = $request->month;
            $date = Carbon::createFromFormat('Y-m', $month);
            $slotQuery = $slotQuery
                ->whereYear('class_date', $date->year)
                ->whereMonth('class_date', $date->month);
        }

        if ($request->date) {
            $slotQuery = $slotQuery->whereDate('class_date', $request->date);
        }

        // Get unique dates for pagination
        $datesQuery = clone $slotQuery;
        $datesPaginated = $datesQuery
            ->select('class_date')
            ->groupBy('class_date')
            ->orderBy('class_date', 'asc')
            ->paginate($perPage);

        $dates = $datesPaginated->getCollection()->pluck('class_date')->toArray();

        // Fetch all slots for these dates
        $slots = OneOnOneClassSlot::where('tutor_id', $tutorId)
            ->whereIn('class_date', $dates)
            ->orderBy('class_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Group slots by formatted date
        $groupedSlots = $slots->groupBy(function ($slot) {
            return \Carbon\Carbon::parse($slot->class_date)->format('F d, Y');
        });

        // Transform grouped slots to include time range
        $formattedSlots = $groupedSlots->map(function ($slots) {
            return $slots->map(function ($slot) {
                $startTime = Carbon::parse($slot->start_time)->format('h:i A');
                $endTime   = Carbon::parse($slot->end_time)->format('h:i A');
                return [
                    'id' => $slot->id,
                    'time_range' => $slot->start_time . ' - ' . $slot->end_time,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_free_trial' => $slot->is_free_trial,
                    'is_active' => $slot->is_active ?? true,
                    'created_at' => $slot->created_at,
                    'updated_at' => $slot->updated_at,
                ];
            })->toArray();
        });

        return jsonResponse(
            true,
            'Slots fetched successfully',
            [
                'slots' => $formattedSlots,
                'pagination' => [
                    'total' => $datesPaginated->total(),
                    'per_page' => $datesPaginated->perPage(),
                    'current_page' => $datesPaginated->currentPage(),
                    'last_page' => $datesPaginated->lastPage(),
                ]
            ]
        );
	}

    /**
     * Create a new slot or multiple slots based on date range
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_date'    => ['required', 'date_format:Y-m-d'],
            'to_date'      => ['nullable', 'date_format:Y-m-d'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'is_free_trial'=> ['boolean'],
        ]);

        $tutorId = auth('sanctum')->user()->id;
        $fromDate = $data['from_date'];
        $toDate = $data['to_date'] ?? $fromDate;

        // If to_date is blank or same as from_date, create single slot
        $isSingleSlot = empty($data['to_date']) || $fromDate === $toDate;

        if ($isSingleSlot) {
            $dates = [$fromDate];
        } else {
            // Generate date range
            $dates = $this->getDateRange($fromDate, $toDate);
        }

        // Check for overlaps on all dates before creating any slots
        foreach ($dates as $date) {
            $overlap = OneOnOneClassSlot::where('tutor_id', $tutorId)
                ->where('class_date', $date)
                ->where(function ($q) use ($data) {
                    $q->where('start_time', '<', $data['end_time'])
                      ->where('end_time', '>', $data['start_time']);
                })
                ->exists();

            if ($overlap) {
               	return jsonResponse(
					false, 
					'Slot overlaps with an existing slot on ' . $date, 
					[],
					422
				);
            }
        }

        // Create slots for all dates
        $slots = [];
        foreach ($dates as $date) {
            $slot = OneOnOneClassSlot::create([
                'tutor_id'      => $tutorId,
                'class_date'    => $date,
                'start_time'    => $data['start_time'],
                'end_time'      => $data['end_time'],
				'timezone'      => $request->timezone,
                'is_free_trial' => $data['is_free_trial'] ?? false,
            ]);
            $slots[] = $slot;
        }

		return jsonResponse(
			true, 
			count($slots) === 1 ? 'Slot created successfully' : count($slots) . ' slots created successfully',
			['slots'   => $slots, 'count'   => count($slots)],
		);
    }

    /**
     * Generate array of dates between from_date and to_date (inclusive)
     */
    private function getDateRange($fromDate, $toDate)
    {
        $dates = [];
        $current = \DateTime::createFromFormat('Y-m-d', $fromDate);
        $end = \DateTime::createFromFormat('Y-m-d', $toDate);

        while ($current <= $end) {
            $dates[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Update slot
     */
    public function update(Request $request, OneOnOneClassSlot $slot)
    {
		$tutorId = auth('sanctum')->user()->id;

        // Only owner can update
        if ($slot->tutor_id !== $tutorId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent updating booked slot
        if ($slot->booking) {
            return response()->json([
                'message' => 'Booked slot cannot be updated'
            ], 422);
        }

        $data = $request->validate([
            'class_date'   => ['required'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'is_free_trial'=> ['boolean'],
            'is_active'    => ['boolean'],
        ]);

        // Overlap check (ignore current slot)
        $overlap = OneOnOneClassSlot::where('tutor_id', $tutorId)
            ->where('class_date', $data['class_date'])
            ->where('id', '!=', $slot->id)
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Slot overlaps with an existing slot'
            ], 422);
        }

        $slot->update($data);

        return response()->json([
            'message' => 'Slot updated successfully',
            'slot'    => $slot
        ]);
    }

    /**
     * Delete slot
     */
    public function destroy(OneOnOneClassSlot $slot)
    {
		$tutorId = auth('sanctum')->user()->id;

        if ($slot->tutor_id !== $tutorId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
		
		$booking = OneOnOneClassBooking::where('slot_id',$slot->id)->count();
        if ($booking > 0) {
            return jsonResponse(
				false, 
				'Booked slot cannot be deleted', 
				[],
				422
			);
        }

        $slot->delete();

		return jsonResponse(
			true, 
			'Slot deleted successfully', 
			[],
		);
    }
}
