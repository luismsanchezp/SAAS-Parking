<?php

namespace Tests\Feature;

use App\Models\ParkingLot;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class StoreTicketTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_create_valid_ticket_where_both_parking_spot_and_vehicle_have_no_ticket_history()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpot = $parkingLot->parkingSpots->first();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();
        $dateNow = Carbon::now()->toDateTimeString();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicle->id/tickets", [
                'parking_spot_id' => $parkingSpot->id
            ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'entry_date' => $dateNow,
                    'remove_date' => null,
                    'parking_spot' => [
                        'id' => $parkingSpot->id,
                        'row' => $parkingSpot->row,
                        'column' => $parkingSpot->column,
                    ],
                    'license_plate' => $vehicle->license_plate,
                    'vehicle_type' => $vehicle->vehicle_type->type,
                    'tariff' => $vehicle->vehicle_type->tariff,
                ]
            ]);
        $this->assertDatabaseHas('tickets', [
            'id' => json_decode($response->getContent(), true)['data']['id'],
            'entry_date' => $dateNow,
            'remove_date' => null,
            'parking_spot_id' => $parkingSpot->id,
            'vehicle_id' => $vehicle->id
        ]);
    }

    public function test_create_valid_ticket_where_both_parking_spot_and_vehicle_have_a_ticket_history_but_are_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpot = $parkingLot->parkingSpots->first();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        for ($i = 0; $i < 3; $i++) {
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id+1,
                'vehicle_id' => $vehicle->id
            ]);
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id,
                'vehicle_id' => $vehicle->id+1
            ]);
        }

        $dateNow = Carbon::now()->toDateTimeString();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicle->id/tickets", [
                'parking_spot_id' => $parkingSpot->id
            ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'entry_date' => $dateNow,
                    'remove_date' => null,
                    'parking_spot' => [
                        'id' => $parkingSpot->id,
                        'row' => $parkingSpot->row,
                        'column' => $parkingSpot->column,
                    ],
                    'license_plate' => $vehicle->license_plate,
                    'vehicle_type' => $vehicle->vehicle_type->type,
                    'tariff' => $vehicle->vehicle_type->tariff,
                ]
            ]);
        $this->assertDatabaseHas('tickets', [
            'id' => json_decode($response->getContent(), true)['data']['id'],
            'entry_date' => $dateNow,
            'remove_date' => null,
            'parking_spot_id' => $parkingSpot->id,
            'vehicle_id' => $vehicle->id
        ]);
    }

    public function test_create_invalid_ticket_where_vehicle_does_not_belong_to_any_users_parking_lots()
    {
        $this->seed();

        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();

        $userB = User::where('id', $userA->id+1)->get()->first();
        $parkingLotUserB = $userB->parkingLots->first();
        $personUserB = $parkingLotUserB->persons->first();
        $vehicleUserB = $personUserB->vehicles->first();

        $num_tickets = Ticket::count();

        $response = $this->actingAs($userA)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicleUserB->id/tickets", [
                'parking_spot_id' => $parkingSpotUserA->id
            ])
            ->assertStatus(403)
            ->assertJson([
                'error' => 'You cannot create tickets with vehicles from parking lots you do not own.'
            ]);
        $this->assertDatabaseCount('tickets', $num_tickets);
    }

    public function test_create_invalid_ticket_where_parking_spot_does_not_belong_to_any_users_parking_lots()
    {
        $this->seed();

        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $userB = User::where('id', $userA->id+1)->get()->first();
        $parkingLotUserB = $userB->parkingLots->first();
        $parkingSpotUserB = $parkingLotUserB->parkingSpots->first();

        $num_tickets = Ticket::count();

        $response = $this->actingAs($userA)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicleUserA->id/tickets", [
                'parking_spot_id' => $parkingSpotUserB->id
            ])
            ->assertStatus(403)
            ->assertJson([
                'error' => 'You cannot use parking spots from other parking lots you do not own.'
            ]);
        $this->assertDatabaseCount('tickets', $num_tickets);
    }

    public function test_create_invalid_ticket_where_vehicle_does_not_belong_to_the_selected_parking_lot()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLotA = $user->parkingLots->first();
        $person = $parkingLotA->persons->first();
        $vehicle = $person->vehicles->first();

        $parkingLotB = ParkingLot::where('id', $parkingLotA->id+1)->get()->first();
        $parkingSpot = $parkingLotB->parkingSpots->first();

        $num_tickets = Ticket::count();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicle->id/tickets", [
                'parking_spot_id' => $parkingSpot->id
            ])
            ->assertStatus(403)
            ->assertJson([
                'error' => 'Vehicle and parking spot does not belong to the same parking lot.'
            ]);
        $this->assertDatabaseCount('tickets', $num_tickets);
    }

    public function test_create_invalid_ticket_where_parking_spot_is_not_available()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpot = $parkingLot->parkingSpots->first();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        for ($i = 0; $i < 3; $i++) {
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id+1,
                'vehicle_id' => $vehicle->id
            ]);
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id,
                'vehicle_id' => $vehicle->id+1
            ]);
        }
        Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+10000),
            'remove_date' => null,
            'parking_spot_id' => $parkingSpot->id,
            'vehicle_id' => $vehicle->id+1
        ]);

        $num_tickets = Ticket::count();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicle->id/tickets", [
                'parking_spot_id' => $parkingSpot->id
            ])
            ->assertStatus(406)
            ->assertJson([
                'error' => 'Parking Spot is not available.'
            ]);
        $this->assertDatabaseCount('tickets', $num_tickets);
    }

    public function test_create_invalid_ticket_where_vehicle_is_already_parked()
    {
        $this->seed();

        $user = User::orderBy('id', 'asc')->get()->first();
        $parkingLot = $user->parkingLots->first();
        $parkingSpot = $parkingLot->parkingSpots->first();
        $person = $parkingLot->persons->first();
        $vehicle = $person->vehicles->first();

        for ($i = 0; $i < 3; $i++) {
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id+1,
                'vehicle_id' => $vehicle->id
            ]);
            Ticket::factory()->create([
                'parking_spot_id' => $parkingSpot->id,
                'vehicle_id' => $vehicle->id+1
            ]);
        }
        Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+10000),
            'remove_date' => null,
            'parking_spot_id' => $parkingSpot->id+1,
            'vehicle_id' => $vehicle->id
        ]);

        $num_tickets = Ticket::count();

        $response = $this->actingAs($user)
            ->withHeaders(['accept' => 'application/json'])
            ->post("/api/v1/vehicles/$vehicle->id/tickets", [
                'parking_spot_id' => $parkingSpot->id
            ])
            ->assertStatus(406)
            ->assertJson([
                'error' => 'Vehicle is already parked.'
            ]);
        $this->assertDatabaseCount('tickets', $num_tickets);
    }
}
