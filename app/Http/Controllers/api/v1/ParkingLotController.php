<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\api\v1\ParkingLotStoreRequest;
use App\Http\Requests\api\v1\ParkingLotIndexRequest;
use App\Http\Requests\api\v1\ParkingLotUpdateRequest;
use App\Http\Resources\ParkingLotResource;

use App\Enums\VehicleTypeEnum;

use Illuminate\Support\Facades\DB;

class ParkingLotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user, ParkingLotIndexRequest $request){
        if ($request->exists('name'))
        {
            $name = $request->input('name');
            $parkingLots = ParkingLot::findByParkingLotName($name, $user->id);
        } else {
            $parkingLots = ParkingLot::findByOwnerId($user->id);
        }
        if ($parkingLots != null) {
            return response()->json([
                'data' => ParkingLotResource::collection($parkingLots)
            ], 200);
        } else {
            return response()->json(['data' => []], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ParkingLotStoreRequest $request, User $user)
    {
        $id = Auth::user()->id;
        if ($user->id == $id){
            $name = $request->input('name');
            $rows = $request->input('rows');
            $columns = $request->input('columns');
            $owner_id = $id;
            $parkingLot = ParkingLot::create([
                'name'=>$name,
                'rows'=>$rows,
                'columns'=>$columns,
                'owner_id'=>$owner_id
            ]);
            $this->createParkingSpots($parkingLot);
            return (new ParkingLotResource($parkingLot))
                ->response()
                ->setStatusCode(201);
        } else {
            return response()->json(['error' => 'You cannot create Parking Lots to other users.'])
                ->setStatusCode(403);
        }
    }

    private function createParkingSpots(ParkingLot $parkingLot) {
        for($r = 1; $r <= $parkingLot->rows; $r++){
            for($c = 1; $c <= $parkingLot->columns; $c++){
                ParkingSpot::create([
                    'row'=>$r,
                    'column'=>$c,
                    'parking_lot_id'=>$parkingLot->id
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ParkingLot  $parkingLot
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, ParkingLot $parkingLot, Request $request)
    {
        if ($parkingLot->owner_id == $user->id){
            if ($request->exists('stats')){
                return response()->json([
                    'data' => $this->getParkingLotStatistics($parkingLot)
                ], 200);
            } else {
                return (new ParkingLotResource($parkingLot
                    ->loadMissing('user')
                    ->loadMissing('persons')))
                    ->response()
                    ->setStatusCode(200);
            }
        } else {
            return response()->json([
                'error'=>'The parking lot id '.$parkingLot->id.' does not belong to the user id '.$user->id
            ], 406);
        }
    }

    private function getParkingLotStatistics(ParkingLot $parkingLot) {
        $cars = $this->countParkedVehiclesByType(VehicleTypeEnum::car->value, $parkingLot->id);
        $motorbikes = $this->countParkedVehiclesByType(VehicleTypeEnum::motorbike->value, $parkingLot->id);
        $free_spots = $this->countFreeParkingSpots($parkingLot->id);
        return [
            'parked_vehicles' => [
                'cars' => $cars,
                'motorbikes' => $motorbikes
            ],
            'free_spots' => $free_spots
        ];
    }

    private function countFreeParkingSpots(int $parkingLotId) {
        return DB::table('parking_spots')
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
            ->count();
    }

    private function countParkedVehiclesByType(string $vehicle_type, int $parkingLotId) {
        return DB::table('vehicles')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'vehicles.vehicle_type_id')
            ->join('tickets', 'tickets.vehicle_id', '=', 'vehicles.id')
            ->where('vehicle_types.parking_lot_id', '=', $parkingLotId)
            ->where('vehicle_types.type', '=', $vehicle_type)
            ->where('tickets.remove_date', '=', NULL)->count();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ParkingLot  $parkingLot
     * @return \Illuminate\Http\Response
     */
    public function update(ParkingLotUpdateRequest $request, User $user, ParkingLot $parkingLot)
    {
        $id = Auth::user()->id;
        if ($id == $parkingLot->owner_id){
            if ($user->id == $parkingLot->owner_id){
                if ($request->exists('name')){
                    $name = $request->input('name');
                    $parkingLot->name = $name;
                }
                $parkingLot->save();

                return (new ParkingLotResource($parkingLot))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json([
                    'error'=>'This parking lot with ID '.$parkingLot->id.' does not belong to this user.'
                ], 406);
            }
        } else {
            return response()->json(['error' => 'You do not own this parking lot.'])
                ->setStatusCode(403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ParkingLot  $parkingLot
     * @return \Illuminate\Http\Response
     */
    public function destroy(ParkingLot $parkingLot)
    {
        return response()->json(['error' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
