<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\api\v1\ParkingLotStoreRequest;
use App\Http\Requests\api\v1\ParkingLotIndexRequest;
use App\Http\Requests\api\v1\ParkingLotUpdateRequest;
use App\Http\Resources\ParkingLotResource;

use App\Enums\VehicleTypeEnum;

class ParkingLotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ParkingLotIndexRequest $request)
    {
        if ($request->exists('name'))
        {
            $parkingLots = ParkingLot::findByName($request->input('name'));
            return response()->json(['data' => ParkingLotResource::collection($parkingLots)], 200);
        } else {
            $parkingLots = ParkingLot::orderBy('name', 'asc')->get();
            return response()->json(['data' => ParkingLotResource::collection($parkingLots)], 200);
        }
    }

    public function indexByUser(User $user, ParkingLotIndexRequest $request){
        if ($request->exists('name'))
        {
            $name = $request->input('name');
            $parkingLots = ParkingLot::findByNameOfUser($name, $user->id);
            return response()->json(['data' => ParkingLotResource::collection($parkingLots)], 200);
        } else {
            $parkingLots = ParkingLot::findByOwnerId($user->id);
            return response()->json(['data' => ParkingLotResource::collection($parkingLots)], 200);
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
            $parkingLot = ParkingLot::create(['name'=>$name,
                'rows'=>$rows, 'columns'=>$columns, 'owner_id'=>$owner_id]);
            for($r = 1; $r <= $rows; $r++){
                for($c = 1; $c <= $columns; $c++){
                    ParkingSpot::create(['row'=>$r, 'column'=>$c,'parking_lot_id'=>$parkingLot->id]);
                }
            }
            return (new ParkingLotResource($parkingLot))
                ->response()
                ->setStatusCode(200);
        } else {
            return response()->json(['data' => 'You cannot create parking lots to other users.'])
                ->setStatusCode(403);
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
            if ($request->exists('statistics')){
                $cars = 0;
                $bikes = 0;
                $free_spots = 0;
                $spots = ParkingSpot::where('parking_lot_id', $parkingLot->id)->get();
                foreach ($spots as $p) {
                    $res = $this->getParkedVehicleType($p);
                    if ($res != NULL){
                        if ($res->type == "Car"){
                            $cars++;
                        } elseif ($res->type == "Motorbike") {
                            $bikes++;
                        }
                    } else {
                        $free_spots++;
                    }
                }
                return response()->json(['data'=>['cars' => $cars, 'motorbikes' => $bikes, 'free_spots' => $free_spots]]
                    , 200);
            } else {
                return (new ParkingLotResource($parkingLot))
                    ->response()
                    ->setStatusCode(200);
            }
        } else {
            return response()->json(['message'=>'This parking lot with ID '.$parkingLot->id.' does not belong to this user.'], 406);
        }
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
        if ($user->id == $parkingLot->owner_id){
            $id = Auth::user()->id;
            if ($id == $parkingLot->owner_id){
                if ($request->exists('name')){
                    $name = $request->input('name');
                    $parkingLot->name = $name;
                }
                $parkingLot->save();

                return (new ParkingLotResource($parkingLot))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['data' => 'You do not own this parking lot.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['message'=>'This parking lot with ID '.$parkingLot->id.' does not belong to this user.'], 406);
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
        return response()->json(['data' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }

    public function getParkedVehicleType(ParkingSpot $parkingSpot){
        $most_recent_ticket = Ticket::where('parking_spot_id', $parkingSpot->id)->orderBy('entry_date', 'desc')->limit(1)->get()->first();
        $spot_tickets_count = Ticket::where('parking_spot_id', $parkingSpot->id)->get()->count();
        if ($spot_tickets_count == 0){
            return NULL;
        } elseif ($most_recent_ticket->remove_date == NULL){
            $vehicle_type_id = Vehicle::where('id', $most_recent_ticket->vehicle_id)->get()->first()->vehicle_type_id;
            return VehicleType::where('id', $vehicle_type_id)->get()->first();
        } else {
            return NULL;
        }
    }
}
