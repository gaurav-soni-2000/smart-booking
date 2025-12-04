<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SlotService;
use App\Models\Service;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\WorkingHour;
use App\Models\Booking;


class BookingController extends Controller
{
    protected $slotService;

    public function __construct(SlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    // GET /api/services
    public function services()
    {
        return response()->json(Service::all());
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required'
        ]);

        $date = Carbon::parse($request->date);

        // Example working hours logic
        $working = WorkingHour::where('weekday', $date->dayOfWeek)->first();
        if (!$working) {
            return response()->json(['slots' => []]);
        }

        $start = Carbon::parse($working->start_time);
        $end = Carbon::parse($working->end_time);

        $slots = [];

        while ($start < $end) {
            $slot = $start->format('H:i');

            $exists = Booking::where('date', $date->format('Y-m-d'))
                ->where('time', $slot)
                ->exists();

            if (!$exists) {
                $slots[] = $slot;
            }

            $start->addMinutes(30);
        }

        return response()->json(['slots' => $slots]);
    }

    // GET /api/slots?date=YYYY-MM-DD&service_id=1
    public function slots(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'service_id' => 'required|integer|exists:services,id'
        ]);

        $slots = $this->slotService->getAvailableSlots($request->date, (int)$request->service_id);
        return response()->json(['slots' => $slots]);
    }

    // POST /api/book
    public function book(Request $request)
    {
        $payload = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i:s',
            'client_email' => 'required|email',
            'client_name' => 'nullable|string'
        ]);

        try {
            $booking = $this->slotService->createBooking($payload);
            return response()->json(['booking' => $booking], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
