<?php

namespace Tests\Feature;

use App\Models\ParkingSpot;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParkingSpotTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_parking_spots_when_there_is_no_ticket_history()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->loadMissing('tickets')->toArray();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/parkinglots/$parkingLot->id/parkingspots?free_spots=TRUE", [])
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $parkingSpots,
            ]);
    }

    public function test_get_available_parking_spots_when_there_is_a_ticket_history_but_all_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();

        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        foreach ($parkingSpots as $parkingSpot) {
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot['id'],
                'vehicle_id' => $vehicle->id
            ]);
        }

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/parkinglots/$parkingLot->id/parkingspots?free_spots=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => $parkingSpots,
            ]);
    }

    public function test_get_available_parking_spots_when_there_is_a_ticket_history_but_none_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();

        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        foreach ($parkingSpots as $parkingSpot) {
            Ticket::factory()->create([
                'remove_date' => null,
                'parking_spot_id' => $parkingSpot['id'],
                'vehicle_id' => $vehicle->id
            ]);
        }

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/parkinglots/$parkingLot->id/parkingspots?free_spots=TRUE", [])
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [],
            ]);
    }

    public function test_get_available_parking_spots_when_there_is_a_ticket_history_some_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $num_parking_spots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)->count();

        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        for ($i = 3; $i < $num_parking_spots-1; $i++) {
            Ticket::factory()->create([
                'remove_date' => null,
                'parking_spot_id' => $parkingSpots[$i]['id'],
                'vehicle_id' => $vehicle->id
            ]);
        }

        Ticket::factory()->create([
            'parking_spot_id' => $parkingSpots[$num_parking_spots-1]['id'],
            'vehicle_id' => $vehicle->id
        ]);

        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/parkinglots/$parkingLot->id/parkingspots?free_spots=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [$parkingSpots[0], $parkingSpots[1]],
            ]);
    }
}
