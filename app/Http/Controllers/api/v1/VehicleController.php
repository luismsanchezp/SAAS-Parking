<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\Vehicle;
use App\Models\Person;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\api\v1\VehicleStoreRequest;
use App\Http\Resources\VehicleResource;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Person $person)
    {
        $id = Auth::user()->id;
        $owner_id = ParkingLot::where('id', $person->parking_lot_id)->get()->first()->owner_id;
        if ($owner_id == $id){
            $vehicles = Vehicle::where('person_id', $person->id)->get();
            return response()->json(['data' => VehicleResource::collection($vehicles)], 200);
        } else {
            return response()->json(['data' => 'This person does not belong to your parking lot.'])
                ->setStatusCode(403);
        }
    }

    public function get_vehicle_by_license_plate(ParkingLot $parkingLot, Request $request) {
        $id = Auth::user()->id;
        if ($parkingLot->owner_id == $id){
            $license_plate = $request->input('license_plate');
            $vehicle = Vehicle::where('license_plate', $license_plate)->get()->first();
            if ($vehicle != NULL) {
                return (new VehicleResource($vehicle))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['data' => 'Vehicle not found.'])
                    ->setStatusCode(404);
            }
        } else {
            return response()->json(['data' => 'This vehicle does not belong to your parking lot.'])
                ->setStatusCode(403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Person $person, VehicleStoreRequest $request)
    {
        $id = Auth::user()->id;
        $owner_id = ParkingLot::where('id', $person->parking_lot_id)->get()->first()->owner_id;
        if ($owner_id == $id){
            $vehicle_type_id = $request->input('vehicle_type_id');
            $vehicle_type = VehicleType::where('id', $vehicle_type_id)->get()->first();
            $owner_id = ParkingLot::where('id', $vehicle_type->parking_lot_id)->get()->first()->owner_id;
            if($owner_id == $id){
                $license_plate = $request->input('license_plate');
                $color = $request->input('color');
                $person_id = $person->id;
                $vehicle_type_id = $request->input('vehicle_type_id');
                $vehicle = Vehicle::create(['license_plate'=>$license_plate,
                    'color'=>$color, 'person_id'=>$person_id, 'vehicle_type_id'=>$vehicle_type_id]);
                return (new VehicleResource($vehicle))
                    ->response()
                    ->setStatusCode(201);
            } else {
                return response()->json(['data' => 'You cannot use vehicle types from other parking lots.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['data' => 'You cannot create vehicles to customers to parking lots that do not belong to you.'])
                ->setStatusCode(403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Person $person, Vehicle $vehicle)
    {
        $id = Auth::user()->id;
        $owner_id = ParkingLot::where('id', $person->parking_lot_id)->get()->first()->owner_id;
        if ($owner_id == $id){
            if($vehicle->person_id == $person->id){
                return (new VehicleResource($vehicle))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['data' => 'This vehicle does not belong to the person.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json(['data' => 'This person does not belong to your parking lot.'])
                ->setStatusCode(403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }
}
