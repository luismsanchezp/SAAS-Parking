<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use App\Models\ParkingLot;
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
    public function index(ParkingLot $parkingLot)
    {
        $user_id = Auth::user()->id;
        if ($parkingLot->owner_id == $user_id){
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
}
