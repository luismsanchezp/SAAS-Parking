<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ParkingSpotResource;
use Illuminate\Support\Facades\DB;

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
            $parkingSpots = ParkingSpot::where('parking_lot_id', $parkingLot->id)->get();
            if ($request->exists('free_spots')){
                if ($request->input('free_spots')){
                    $parkingSpots = $this->getFreeParkingSpots($parkingLot->id);
                    return response()->json([
                        'data' => ParkingSpotResource::collection($parkingSpots)
                    ], 200);
                }
            }
            return response()->json([
                'data' => ParkingSpotResource::collection($parkingSpots->loadMissing('tickets'))
            ], 200);
        } else {
            return response()->json(['error' => 'You do not own this parking lot.'])
                ->setStatusCode(403);
        }
    }

    private function getFreeParkingSpots(int $parkingLotId){
        $ids = DB::table('parking_spots')
            ->select('parking_spots.id')
            ->distinct('parking_spots.id')
            ->leftJoin('tickets', 'tickets.parking_spot_id', '=', 'parking_spots.id')
            ->where(function ($query) {
                $query->select('remove_date')
                    ->from('tickets')
                    ->whereColumn('tickets.parking_spot_id', 'parking_spots.id')
                    ->orderByDesc('entry_date')
                    ->limit(1);
            }, '!=', NULL)
            ->orWhere('tickets.parking_spot_id', NULL)
            ->where('parking_spots.parking_lot_id', '=', $parkingLotId)
            ->get();
        $models = collect([]);
        foreach ($ids as $id) {
            $models->push(ParkingSpot::find($id->id)->loadMissing('tickets'));
        }
        return $models;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json(['error' => 'Store method is not allowed.'])
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
                return (new ParkingSpotResource($parkingSpot
                    ->loadMissing('parking_lot')
                    ->loadMissing('tickets')))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json([
                    'error'=>'This parking lot with ID '.$parkingLot->id.' does not belong to this parking spot.'
                ], 406);
            }
        } else {
            return response()->json(['error'=>'You do not own this parking lot.'], 403);
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
        return response()->json(['error' => 'Update method is not allowed.'])
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
        return response()->json(['error' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
