<?php

namespace App\Services;

use App\Models\WorkingRule;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class SlotService
{
    /**
     * Get available slots for a date and service.
     * Returns array of ['start'=>'HH:MM:SS','end'=>'HH:MM:SS']
     */
    public function getAvailableSlots(string $date, int $serviceId): array
    {
        $service = Service::findOrFail($serviceId);
        $duration = (int)$service->duration_minutes;

        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        $weekday = $carbonDate->dayOfWeek; // 0-6

        $rules = WorkingRule::where('weekday', $weekday)->get();
        $slots = [];

        foreach ($rules as $rule) {
            $start = Carbon::parse($rule->start_time)
                ->setDate($carbonDate->year, $carbonDate->month, $carbonDate->day);
            $end = Carbon::parse($rule->end_time)
                ->setDate($carbonDate->year, $carbonDate->month, $carbonDate->day);

            $interval = (int)$rule->slot_interval;

            // iterate from $start to latest possible start that fits duration
            for ($current = $start->copy(); $current->lte($end->copy()->subMinutes($duration)); $current->addMinutes($interval)) {
                $slotStart = $current->copy();
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                // skip past slots if date is today
                if ($slotStart->lt(now())) {
                    continue;
                }

                // check overlap with existing bookings (same service)
                $overlap = Booking::where('service_id', $serviceId)
                    ->where('date', $date)
                    ->where(function ($q) use ($slotStart, $slotEnd) {
                        $q->whereBetween('start_time', [$slotStart->toTimeString(), $slotEnd->toTimeString()])
                          ->orWhereBetween('end_time', [$slotStart->toTimeString(), $slotEnd->toTimeString()])
                          ->orWhere(function ($q2) use ($slotStart, $slotEnd) {
                              $q2->where('start_time', '<', $slotStart->toTimeString())
                                 ->where('end_time', '>', $slotEnd->toTimeString());
                          });
                    })->exists();

                if (!$overlap) {
                    $slots[] = [
                        'start' => $slotStart->toTimeString(),
                        'end' => $slotEnd->toTimeString(),
                    ];
                }
            }
        }

        usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));

        return $slots;
    }

    /**
     * Create booking with validation + transaction to reduce race conditions.
     * Throws Exception on validation/overlap error.
     */
    public function createBooking(array $data)
    {
        $service = Service::findOrFail($data['service_id']);
        $date = $data['date'];
        $startTime = $data['start_time'];

        // Construct Carbon start & end
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $startTime);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        if ($start->lt(now())) {
            throw new Exception('Cannot book a past time.');
        }

        // Validate fits in working rule
        $weekday = $start->dayOfWeek;
        $rules = WorkingRule::where('weekday', $weekday)->get();

        $fits = false;
        foreach ($rules as $rule) {
            $ruleStart = Carbon::parse($rule->start_time)->setDate($start->year,$start->month,$start->day);
            $ruleEnd = Carbon::parse($rule->end_time)->setDate($start->year,$start->month,$start->day);

            if ($start->gte($ruleStart) && $end->lte($ruleEnd)) {
                $fits = true;
                break;
            }
        }
        if (!$fits) {
            throw new Exception('Selected time is outside working hours.');
        }

        // Use transaction to reduce race conditions.
        return DB::transaction(function () use ($service, $date, $start, $end, $data) {
            // re-check overlaps inside transaction
            $exists = Booking::where('service_id', $service->id)
                ->where('date', $date)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start->toTimeString(), $end->toTimeString()])
                      ->orWhereBetween('end_time', [$start->toTimeString(), $end->toTimeString()])
                      ->orWhere(function ($q2) use ($start, $end) {
                          $q2->where('start_time', '<', $start->toTimeString())
                             ->where('end_time', '>', $end->toTimeString());
                      });
                })->lockForUpdate()->exists();

            if ($exists) {
                throw new Exception('Slot already booked.');
            }

            $booking = \App\Models\Booking::create([
                'service_id' => $service->id,
                'date' => $date,
                'start_time' => $start->toTimeString(),
                'end_time' => $end->toTimeString(),
                'client_email' => $data['client_email'],
                'client_name' => $data['client_name'] ?? null,
            ]);

            return $booking;
        }, 5);
    }
}
