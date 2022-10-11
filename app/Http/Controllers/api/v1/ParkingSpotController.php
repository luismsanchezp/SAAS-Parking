<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use App\Models\ParkingLot;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ParkingSpotResource;

class ParkingSpotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ParkingLot $parkingLot, Request $request)
    {
        $user_id = Auth::user()->id;
        if ($parkingLot->owner_id == $user_id){
            if ($request->exists('free_spots')){
                if ($request->input('free_spots')){
                    $freeParkingSpots = collect([]);
                    $spots = ParkingSpot::where('parking_lot_id', $parkingLot->id)->get();
                    foreach ($spots as $p) {
                        $res = $this->getFreeParkingSpot($p);
                        if ($res != NULL){
                            $freeParkingSpots->push($res);
                        }
                    }
                    return response()->json(['data' => ParkingSpotResource::collection($freeParkingSpots)], 200);
                }
            }
            $parkingSpots = ParkingSpot::where('parking_lot_id', $parkingLot->id)->get();
            return response()->json(['data' => ParkingSpotResource::collection($parkingSpots)], 200);
        } else {
            return response()->json(['data' => 'You do not own this parking lot.'])
                ->setStatusCode(403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json(['data' => 'Store method is not allowed.'])
            ->setStatusCode(405);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkingSpot  $parkingSpot
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingLot $parkingLot, ParkingSpot $parkingSpot)
    {
        $user_id = Auth::user()->id;
        if ($parkingLot->owner_id == $user_id){
            if ($parkingSpot->parking_lot_id == $parkingLot->id){
                return (new ParkingSpotResource($parkingSpot))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['message'=>'This parking lot with ID '.$parkingLot->id.' does not belong to this parking spot.'], 406);
            }
        } else {
            return response()->json(['message'=>'You do not own this parking lot.'], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingSpot  $parkingSpot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ParkingSpot $parkingSpot)
    {
        return response()->json(['data' => 'Update method is not allowed.'])
            ->setStatusCode(405);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingSpot  $parkingSpot
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParkingSpot $parkingSpot)
    {
        return response()->json(['data' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }

    public function getFreeParkingSpot(ParkingSpot $parkingSpot){
        $most_recent_ticket = Ticket::where('parking_spot_id', $parkingSpot->id)->orderBy('entry_date', 'desc')->limit(1)->get()->first();
        $spot_tickets_count = Ticket::where('parking_spot_id', $parkingSpot->id)->get()->count();
        if ($spot_tickets_count == 0){
            return $parkingSpot;
        } elseif ($most_recent_ticket->remove_date != NULL){
            return $parkingSpot;
        } else {
            return NULL;
        }
    }

    public function getAllFreeParkingSpots(ParkingLot $parkingLot){
        $user_id = Auth::user()->id;
        if ($parkingLot->owner_id == $user_id){

        } else {
            return response()->json(['message'=>'You do not own this parking lot.'], 403);
        }
    }
}
