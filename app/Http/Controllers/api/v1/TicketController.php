<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use App\Models\Ticket;
use App\Models\Vehicle;
use App\Models\Person;
use Illuminate\Http\Request;

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
        $person = Person::where('id', $vehicle->person_id)->get()->first();
        $parkingLot = ParkingLot::where('id', $person->parking_lot_id)->get()->first();
        if ($parkingLot->owner_id == $id){
            $tickets = Ticket::where('vehicle_id', $vehicle->id)->get();
            return response()->json(['data' => TicketResource::collection($tickets)], 200);
        } else {
            return response()->json(['data' => 'You cannot get tickets of vehicles from other parking lots.'])
                ->setStatusCode(403);
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
        $id = Auth::user()->id;
        $person = Person::where('id', $vehicle->person_id)->get()->first();
        $parkingLot = ParkingLot::where('id', $person->parking_lot_id)->get()->first();
        if ($parkingLot->owner_id == $id){
            $parking_spot_id = $request->input('parking_spot_id');
            $parkingSpot = ParkingSpot::where('id', $parking_spot_id)->get()->first();
            $parkingLot = ParkingLot::where('id', $parkingSpot->parking_lot_id)->get()->first();
            if($parkingLot->owner_id == $id){
                $most_recent_ticket = Ticket::where('parking_spot_id', $parkingSpot->id)->orderBy('entry_date', 'desc')->limit(1)->get()->first();
                $entry_date = Carbon::now()->toDateTimeString();
                $remove_date = NULL;
                $parking_spot_id = $request->input('parking_spot_id');
                if(Ticket::where('parking_spot_id', $parkingSpot->id)->get()->count() == 0){
                    $ticket = Ticket::create(['entry_date'=>$entry_date,
                        'remove_date'=>$remove_date, 'parking_spot_id'=>$parking_spot_id,
                        'vehicle_id'=>$vehicle->id]);
                    return (new TicketResource($ticket))
                        ->response()
                        ->setStatusCode(200);
                } elseif($most_recent_ticket->remove_date != NULL) {
                    $ticket = Ticket::create(['entry_date'=>$entry_date,
                        'remove_date'=>$remove_date, 'parking_spot_id'=>$parking_spot_id,
                        'vehicle_id'=>$vehicle->id]);
                    return (new TicketResource($ticket))
                        ->response()
                        ->setStatusCode(200);
                } elseif($most_recent_ticket->remove_date == NULL) {
                    return response()->json(['data' => 'Parking Spot is not available.'])
                        ->setStatusCode(406);
                }
            } else {
                return response()->json(['data' => 'You cannot use parking spots from other parking lots.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['data' => 'You cannot create tickets with vehicles from other parking lots.'])
                ->setStatusCode(403);
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
        $id = Auth::user()->id;
        $person = Person::where('id', $vehicle->person_id)->get()->first();
        $parkingLot = ParkingLot::where('id', $person->parking_lot_id)->get()->first();
        if ($parkingLot->owner_id == $id){
            $parkingSpot = ParkingSpot::where('id', $ticket->parking_spot_id)->get()->first();
            $parkingLot = ParkingLot::where('id', $parkingSpot->parking_lot_id)->get()->first();
            if($parkingLot->owner_id == $id){
                return (new TicketResource($ticket))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['data' => 'You cannot get tickets from other parking lots.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['data' => 'You cannot get tickets of vehicles from other parking lots.'])
                ->setStatusCode(403);
        }
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
        $id = Auth::user()->id;
        $person = Person::where('id', $vehicle->person_id)->get()->first();
        $parkingLot = ParkingLot::where('id', $person->parking_lot_id)->get()->first();
        if ($parkingLot->owner_id == $id){
            if($vehicle->id == $ticket->vehicle_id){
                if($ticket->remove_date == NULL){
                    $ticket->remove_date = Carbon::now()->toDateTimeString();
                    $ticket->save();
                    return (new TicketResource($ticket))
                        ->response()
                        ->setStatusCode(200);
                } else {
                    return response()->json(['data' => 'Parking Spot is already free.'])
                        ->setStatusCode(406);
                }
            } else {
                return response()->json(['data' => 'This ticket does not belong to vehicle.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['data' => 'You cannot update tickets with vehicles from other parking lots.'])
                ->setStatusCode(403);
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
        //
    }
}
