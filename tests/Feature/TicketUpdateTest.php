<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_update_of_ticket()
    {
        $this->seed();
        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $entry_date = date('Y-m-d H:i:s', Carbon::now()->timestamp-80000);
        $ticket = Ticket::factory()->create([
            'entry_date' => $entry_date,
            'remove_date' => null,
            'parking_spot_id' => $parkingSpotUserA->id,
            'vehicle_id' => $vehicleUserA->id
        ]);

        $remove_date = Carbon::now();
        $time_elapsed_hours = (date_diff(date_create($ticket->entry_date), $remove_date)->h);
        $total = ($vehicleUserA->vehicle_type->tariff)*$time_elapsed_hours;
        $response = $this->actingAs($userA)
            ->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/vehicles/$vehicleUserA->id/tickets/$ticket->id", [])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $ticket->id,
                    'entry_date' => $entry_date,
                    'remove_date' => $remove_date->toDateTimeString(),
                    'parking_spot' => [
                        'id' => $parkingSpotUserA->id,
                        'row' => $parkingSpotUserA->row,
                        'column' => $parkingSpotUserA->column
                    ],
                    'license_plate' => $vehicleUserA->license_plate,
                    'vehicle_type' => $vehicleUserA->vehicle_type->type,
                    'tariff' => $vehicleUserA->vehicle_type->tariff,
                    'time_elapsed_hours' => $time_elapsed_hours,
                    'total' => $total
                ]
            ]);

        $updated_ticket = Ticket::where('id', $ticket->id)->get()->first();
        $this->assertNotNull($updated_ticket);
        $this->assertEquals($entry_date, $updated_ticket->entry_date);
        $this->assertEquals($remove_date, $updated_ticket->remove_date);
        $this->assertEquals($parkingSpotUserA->id, $updated_ticket->parking_spot_id);
        $this->assertEquals($vehicleUserA->id, $updated_ticket->vehicle_id);
    }

    public function test_invalid_update_where_ticket_does_not_belong_to_any_users_parking_lots()
    {
        $this->seed();
        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $ticket = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+10000),
            'remove_date' => null,
            'parking_spot_id' => $parkingSpotUserA->id,
            'vehicle_id' => $vehicleUserA->id
        ]);

        $userB = User::where('id', $userA->id+1)->get()->first();
        $parkingLotUserB = $userB->parkingLots->first();
        $personUserB = $parkingLotUserB->persons->first();
        $vehicleUserB = $personUserB->vehicles->first();

        $response = $this->actingAs($userB)
            ->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/vehicles/$vehicleUserB->id/tickets/$ticket->id", [])
            ->assertStatus(403)
            ->assertJson([
                'error' => 'Ticket does not belong to any of your parking lots.'
            ]);

        $updated_ticket = Ticket::where('id', $ticket->id)->get()->first();
        $this->assertNotNull($updated_ticket);
        $this->assertEquals($ticket->entry_date, $updated_ticket->entry_date);
        $this->assertEquals($ticket->remove_date, $updated_ticket->remove_date);
        $this->assertEquals($ticket->parking_spot_id, $updated_ticket->parking_spot_id);
        $this->assertEquals($ticket->vehicle_id, $updated_ticket->vehicle_id);
    }

    public function test_invalid_update_of_unparked_car_ticket()
    {
        $this->seed();

        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $ticket = Ticket::factory()->create([
            'parking_spot_id' => $parkingSpotUserA->id,
            'vehicle_id' => $vehicleUserA->id
        ]);

        $response = $this->actingAs($userA)
            ->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/vehicles/$vehicleUserA->id/tickets/$ticket->id", [])
            ->assertStatus(406)
            ->assertJson([
                'error' => 'Ticket is finished.'
            ]);
        $updated_ticket = Ticket::where('id', $ticket->id)->get()->first();
        $this->assertNotNull($updated_ticket);
        $this->assertEquals($ticket->entry_date, $updated_ticket->entry_date);
        $this->assertEquals($ticket->remove_date, $updated_ticket->remove_date);
        $this->assertEquals($ticket->parking_spot_id, $updated_ticket->parking_spot_id);
        $this->assertEquals($ticket->vehicle_id, $updated_ticket->vehicle_id);
    }

    public function test_invalid_update_where_vehicle_does_not_belong_to_any_users_parking_lots()
    {
        $this->seed();

        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $ticket = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+10000),
            'remove_date' => null,
            'parking_spot_id' => $parkingSpotUserA->id,
            'vehicle_id' => $vehicleUserA->id
        ]);

        $userB = User::where('id', $userA->id+1)->get()->first();

        $response = $this->actingAs($userB)
            ->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/vehicles/$vehicleUserA->id/tickets/$ticket->id", [])
            ->assertStatus(403)
            ->assertJson([
                'error' => 'Vehicle does not belong to any of your parking lots.'
            ]);
        $updated_ticket = Ticket::where('id', $ticket->id)->get()->first();
        $this->assertNotNull($updated_ticket);
        $this->assertEquals($ticket->entry_date, $updated_ticket->entry_date);
        $this->assertEquals($ticket->remove_date, $updated_ticket->remove_date);
        $this->assertEquals($ticket->parking_spot_id, $updated_ticket->parking_spot_id);
        $this->assertEquals($ticket->vehicle_id, $updated_ticket->vehicle_id);
    }

    public function test_invalid_update_where_ticket_does_not_belong_to_the_vehicle()
    {
        $this->seed();
        $userA = User::orderBy('id', 'asc')->get()->first();
        $parkingLotUserA = $userA->parkingLots->first();
        $parkingSpotUserA = $parkingLotUserA->parkingSpots->first();
        $personUserA = $parkingLotUserA->persons->first();
        $vehicleUserA = $personUserA->vehicles->first();

        $ticket = Ticket::factory()->create([
            'entry_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+10000),
            'remove_date' => null,
            'parking_spot_id' => $parkingSpotUserA->id,
            'vehicle_id' => $vehicleUserA->id+1
        ]);

        $response = $this->actingAs($userA)
            ->withHeaders(['accept' => 'application/json'])
            ->put("/api/v1/vehicles/$vehicleUserA->id/tickets/$ticket->id", [])
            ->assertStatus(406)
            ->assertJson([
                'error' => 'Ticket does not belong to that vehicle.'
            ]);
        $updated_ticket = Ticket::where('id', $ticket->id)->get()->first();
        $this->assertNotNull($updated_ticket);
        $this->assertEquals($ticket->entry_date, $updated_ticket->entry_date);
        $this->assertEquals($ticket->remove_date, $updated_ticket->remove_date);
        $this->assertEquals($ticket->parking_spot_id, $updated_ticket->parking_spot_id);
        $this->assertEquals($ticket->vehicle_id, $updated_ticket->vehicle_id);
    }
}
