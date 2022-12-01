<?php

namespace Tests\Feature;

use App\Models\ParkingSpot;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParkingLotDailyEarningsTest extends TestCase
{

    use RefreshDatabase;

    public function test_get_parking_lot_daily_earnings_when_no_ticket_history()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'todays_income' => 0
                ],
            ]);
    }

    public function test_get_parking_lot_daily_earnings_when_theres_ticket_history_but_none_today()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        $t1 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-140000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-100000),
            'parking_spot_id' => $parkingSpots[0]['id'],
            'vehicle_id' => $vehicle->id
        ]);

        $t2 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-140000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-100000),
            'parking_spot_id' => $parkingSpots[1]['id'],
            'vehicle_id' => $vehicle->id+1
        ]);

        $t3 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-140000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-100000),
            'parking_spot_id' => $parkingSpots[2]['id'],
            'vehicle_id' => $vehicle->id+2
        ]);

        $tickets = [$t1, $t2, $t3];

        $sum = 0;
        foreach ($tickets as $t) {
            $time_elapsed_hours = (date_diff(date_create($t->entry_date), date_create($t->remove_date))->h);
            $total = ($t->vehicle->vehicle_type->tariff)*$time_elapsed_hours;
            $sum+=$total;
        }

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'todays_income' => 0
                ],
            ]);
    }

    public function test_get_parking_lot_daily_earnings_when_theres_ticket_history_vehicles_unparked_today()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        $t1 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-10000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp),
            'parking_spot_id' => $parkingSpots[0]['id'],
            'vehicle_id' => $vehicle->id
        ]);

        $t2 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-10000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp),
            'parking_spot_id' => $parkingSpots[1]['id'],
            'vehicle_id' => $vehicle->id+1
        ]);

        $t3 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-10000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp),
            'parking_spot_id' => $parkingSpots[2]['id'],
            'vehicle_id' => $vehicle->id+2
        ]);

        $tickets = [$t1, $t2, $t3];

        $sum = 0;
        foreach ($tickets as $t) {
            $time_elapsed_hours = (date_diff(date_create($t->entry_date), date_create($t->remove_date))->h);
            $total = ($t->vehicle->vehicle_type->tariff)*$time_elapsed_hours;
            $sum+=$total;
        }

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'todays_income' => $sum
                ],
            ]);
    }

    public function test_get_parking_lot_daily_earnings_when_theres_ticket_history_some_not_all_vehicles_unparked_today()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        $t1 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-10000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp),
            'parking_spot_id' => $parkingSpots[0]['id'],
            'vehicle_id' => $vehicle->id
        ]);

        Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-200000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-100000),
            'parking_spot_id' => $parkingSpots[1]['id'],
            'vehicle_id' => $vehicle->id+1
        ]);

        $t3 = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-10000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp),
            'parking_spot_id' => $parkingSpots[2]['id'],
            'vehicle_id' => $vehicle->id+2
        ]);

        Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-200000),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp-100000),
            'parking_spot_id' => $parkingSpots[3]['id'],
            'vehicle_id' => $vehicle->id+3
        ]);

        $tickets = [$t1, $t3];

        $sum = 0;
        foreach ($tickets as $t) {
            $time_elapsed_hours = (date_diff(date_create($t->entry_date), date_create($t->remove_date))->h);
            $total = ($t->vehicle->vehicle_type->tariff)*$time_elapsed_hours;
            $sum+=$total;
        }

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'todays_income' => $sum
                ],
            ]);
    }
}
