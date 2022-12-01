<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use App\Models\Ticket;
use App\Models\Vehicle;

use App\Http\Requests\api\v1\TicketStoreRequest;
use App\Http\Resources\TicketResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Vehicle $vehicle)
    {
        $id = Auth::user()->id;
        $parkingLot = $vehicle->person->parking_lot;
        if ($parkingLot->owner_id == $id){
            $tickets = Ticket::where('vehicle_id', $vehicle->id)->get();
            return response()->json([
                'data' => TicketResource::collection($tickets
                    ->loadMissing('parking_spot')
                    ->loadMissing('vehicle')
                )
            ], 200);
        } else {
            return response()->json([
                'error' => 'You cannot get tickets from other parking lots.'
            ])->setStatusCode(403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Vehicle $vehicle, TicketStoreRequest $request)
    {
        $reqParkingSpotId = $request->input('parking_spot_id');
        $checksOne = $this->checkVehicleParkingSpot($vehicle, $reqParkingSpotId);
        if ($checksOne != null) {
            return $checksOne;
        }
        $checksTwo = $this->checkParkingSpotAvailability($reqParkingSpotId);
        if ($checksTwo != null) {
            return $checksTwo;
        }
        $checksThree = $this->checkVehicleParkingStatus($vehicle);
        if ($checksThree != null) {
            return $checksThree;
        }
        $entry_date = Carbon::now()->toDateTimeString();
        $ticket = Ticket::create([
            'entry_date' => $entry_date,
            'remove_date' => NULL,
            'parking_spot_id' => $reqParkingSpotId,
            'vehicle_id' => $vehicle->id
        ]);
        return (new TicketResource($ticket
                ->loadMissing('parking_spot')
                ->loadMissing('vehicle')
            )
        )->response()->setStatusCode(201);
    }

    private function checkVehicleParkingSpot(Vehicle $vehicle, int $parkingSpotId) {
        $id = Auth::user()->id;
        $vehicleParkingLot = $vehicle->person->parking_lot;
        if ($vehicleParkingLot->owner_id == $id){
            $parkingSpot = ParkingSpot::where('id', $parkingSpotId)->get()->first();
            if ($parkingSpot != null) {
                $spotParkingLot = $parkingSpot->parking_lot;
                if ($spotParkingLot->owner_id == $id){
                    if ($vehicle->person->parking_lot->id == $spotParkingLot->id) {
                        return null;
                    } else {
                        return response()->json([
                            'error' => 'Vehicle and parking spot does not belong to the same parking lot.'
                        ])->setStatusCode(403);
                    }
                } else {
                    return response()->json([
                        'error' => 'You cannot use parking spots from other parking lots you do not own.'
                    ])->setStatusCode(403);
                }
            } else {
                return response()->json([
                    'error' => 'Cannot find parking spot.'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'error' => 'You cannot create tickets with vehicles from parking lots you do not own.'
            ])->setStatusCode(403);
        }
    }

    private function checkParkingSpotAvailability(int $parkingSpotId) {
        $spotTicketCount = Ticket::where('parking_spot_id', $parkingSpotId)->count();
        if ($spotTicketCount > 0) {
            $mostRecentTicket = Ticket::where('parking_spot_id', $parkingSpotId)
                ->orderBy('entry_date', 'desc')
                ->limit(1)->get()->first();
            if ($mostRecentTicket->remove_date == null) {
                return response()->json([
                    'error' => 'Parking Spot is not available.'
                ])->setStatusCode(406);
            } else { return null; }
        } else if ($spotTicketCount == 0) {
            return null;
        } else {
            throw new ValueError('Parking spot ticket count value is not 0 or greater.'.$spotTicketCount);
        }
    }

    private function checkVehicleParkingStatus(Vehicle $vehicle) {
        $vehicleTicketCount = $vehicle->tickets->count();
        if ($vehicleTicketCount > 0) {
            $mostRecentTicket = Ticket::where('vehicle_id', $vehicle->id)
                ->orderBy('entry_date', 'desc')
                ->limit(1)->get()->first();
            if ($mostRecentTicket->remove_date == null) {
                return response()->json([
                    'error' => 'Vehicle is already parked.'
                ])->setStatusCode(406);
            } else { return null; }
        } else if ($vehicleTicketCount == 0) {
            return null;
        } else {
            throw new ValueError('Vehicle ticket count value is not 0 or greater.'.$vehicleTicketCount);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle, Ticket $ticket)
    {
        $checks = $this->checkTicketVehicle($vehicle, $ticket);
        if ($checks != null) {
            return $checks;
        }
        return (new TicketResource($ticket
                ->loadMissing('parking_spot')
                ->loadMissing('vehicle')
            )
        )->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Vehicle $vehicle, Ticket $ticket)
    {
        $checks = $this->checkTicketVehicle($vehicle, $ticket);
        if ($checks != null) {
            return $checks;
        }
        if($ticket->remove_date == NULL){
            $ticket->remove_date = Carbon::now()->toDateTimeString();
            $ticket->save();
            return (new TicketResource($ticket
                    ->loadMissing('parking_spot')
                    ->loadMissing('vehicle')
                )
            )->response()->setStatusCode(200);
        } else {
            return response()->json(['error' => 'Ticket is finished.'])
                ->setStatusCode(406);
        }
    }

    private function checkTicketVehicle(Vehicle $vehicle, Ticket $ticket) {
        $id = Auth::user()->id;
        $vehicleParkingLot = $vehicle->person->parking_lot;
        if ($vehicleParkingLot->owner_id == $id){
            $ticketParkingLot = $ticket->parking_spot->parking_lot;
            if ($ticketParkingLot->owner_id == $id){
                if ($ticket->vehicle_id == $vehicle->id) {
                    return null;
                } else {
                    return response()->json([
                        'error' => 'Ticket does not belong to that vehicle.'
                    ])->setStatusCode(406);
                }
            } else {
                return response()->json([
                    'error' => 'Ticket does not belong to any of your parking lots.'
                ])->setStatusCode(403);
            }
        } else {
            return response()->json([
                'error' => 'Vehicle does not belong to any of your parking lots.'
            ])->setStatusCode(403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        return response()->json(['error' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
