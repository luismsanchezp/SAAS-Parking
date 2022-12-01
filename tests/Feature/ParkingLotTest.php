<?php

namespace Tests\Feature;

use App\Models\ParkingSpot;
use App\Models\Person;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ParkingLotTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_parking_lot_stats_when_there_is_no_ticket_history()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $num_parking_spots = $parkingLot->parkingSpots->count();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'parked_vehicles' => [
                        'cars' => 0,
                        'motorbikes' => 0
                    ],
                    'free_spots' => $num_parking_spots
                ],
            ]);
    }

    public function test_get_parking_lot_stats_when_there_is_ticket_history_and_all_spots_are_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $num_parking_spots = $parkingLot->parkingSpots->count();

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
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'parked_vehicles' => [
                        'cars' => 0,
                        'motorbikes' => 0
                    ],
                    'free_spots' => $num_parking_spots
                ],
            ]);
    }

    public function test_get_parking_lot_stats_when_there_is_ticket_history_and_some_spots_are_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();
        $num_parking_spots = $parkingLot->parkingSpots->count();

        $personA = $parkingLot->persons->first();
        $vehicleA = $personA->vehicles->first();
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[0]['id'],
            'vehicle_id' => $vehicleA->id
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[1]['id'],
            'vehicle_id' => $vehicleA->id+2
        ]);
        $personB = Person::where('id', $personA->id+1)->get()->first();
        $vehicleB = $personB->vehicles->first();
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[2]['id'],
            'vehicle_id' => $vehicleB->id
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[3]['id'],
            'vehicle_id' => $vehicleB->id+1
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[4]['id'],
            'vehicle_id' => $vehicleB->id+2
        ]);

        error_log("FUCK".$num_parking_spots);

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'parked_vehicles' => [
                        'cars' => 3,
                        'motorbikes' => 2
                    ],
                    'free_spots' => $num_parking_spots-5
                ],
            ]);
    }

    public function test_get_parking_lot_stats_when_there_is_ticket_history_and_no_single_spot_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpots = ParkingSpot::select('id', 'row', 'column')
            ->where('parking_lot_id', $parkingLot->id)
            ->get()->toArray();

        $personA = $parkingLot->persons->first();
        $vehicleA = $personA->vehicles->first();
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[0]['id'],
            'vehicle_id' => $vehicleA->id
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[1]['id'],
            'vehicle_id' => $vehicleA->id+1
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[2]['id'],
            'vehicle_id' => $vehicleA->id+2
        ]);
        $personB = Person::where('id', $personA->id+1)->get()->first();
        $vehicleB = $personB->vehicles->first();
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[3]['id'],
            'vehicle_id' => $vehicleB->id
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[4]['id'],
            'vehicle_id' => $vehicleB->id+1
        ]);
        Ticket::factory()->create([
            'remove_date' => null,
            'parking_spot_id' => $parkingSpots[5]['id'],
            'vehicle_id' => $vehicleB->id+2
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->get("/api/v1/users/$user->id/parkinglots/$parkingLot->id?stats=TRUE", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'parked_vehicles' => [
                        'cars' => 4,
                        'motorbikes' => 2
                    ],
                    'free_spots' => 0
                ],
            ]);
    }
}
