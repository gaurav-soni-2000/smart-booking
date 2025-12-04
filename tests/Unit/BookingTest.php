<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Service;
use App\Models\WorkingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_slots_and_booking()
    {
        // seed a service
        $service = Service::create(['name'=>'Test Service','duration_minutes'=>30,'price'=>10.00]);

        // make a working rule for next tuesday
        $weekday = Carbon::now()->addWeek()->dayOfWeek;
        WorkingRule::create([
            'weekday' => $weekday,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'slot_interval' => 30
        ]);

        $date = Carbon::now()->addWeek()->format('Y-m-d');

        // fetch slots
        $resp = $this->getJson('/api/slots?date='.$date.'&service_id='.$service->id);
        $resp->assertStatus(200);
        $slots = $resp->json('slots');
        $this->assertNotEmpty($slots);

        // book first slot
        $slot = $slots[0];
        $bookResp = $this->postJson('/api/book', [
            'service_id' => $service->id,
            'date' => $date,
            'start_time' => $slot['start'],
            'client_email' => 'a@b.com'
        ]);
        $bookResp->assertStatus(201);

        // try double booking same slot -> fail
        $bookResp2 = $this->postJson('/api/book', [
            'service_id' => $service->id,
            'date' => $date,
            'start_time' => $slot['start'],
            'client_email' => 'c@d.com'
        ]);
        $bookResp2->assertStatus(422);
    }
}
