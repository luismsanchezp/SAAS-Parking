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
        $parkingLot = $person->parking_lot;
        if ($parkingLot->owner_id == $id){
            $vehicles = Vehicle::where('person_id', $person->id)->get();
            return response()->json(['data' => VehicleResource::collection($vehicles)], 200);
        } else {
            return response()->json(['error' => 'This person does not belong to your parking lots.'])
                ->setStatusCode(403);
        }
    }

    public function getVehicleByLicensePlate(ParkingLot $parkingLot, Request $request) {
        $id = Auth::user()->id;
        if ($parkingLot->owner_id == $id){
            $license_plate = $request->input('license_plate');
            $vehicle = Vehicle::where('license_plate', $license_plate)->get()->first();
            if ($vehicle != NULL) {
                if ($vehicle->person->parking_lot_id == $parkingLot->id) {
                    return (new VehicleResource($vehicle))
                        ->response()
                        ->setStatusCode(200);
                } else {
                    return response()->json(['error' => 'Vehicle does not belong to this parking lot.'])
                        ->setStatusCode(404);
                }
            } else {
                return response()->json(['error' => 'Vehicle not found.'])
                    ->setStatusCode(404);
            }
        } else {
            return response()->json(['error' => 'You do not own this parking lot.'])
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
        $parkingLot = $person->parking_lot;
        if ($parkingLot->owner_id == $id){
            $vehicle_type_str = $request->input('vehicle_type');
            $vehicle_type = VehicleType::getCurrentTariffByType($vehicle_type_str);
            if ($vehicle_type != null) {
                $license_plate = $request->input('license_plate');
                $color = $request->input('color');
                $person_id = $person->id;
                $vehicle_type_id = $vehicle_type->id;
                $vehicle = Vehicle::create([
                    'license_plate'=>$license_plate,
                    'color'=>$color,
                    'person_id'=>$person_id,
                    'vehicle_type_id'=>$vehicle_type_id
                ]);
                return (new VehicleResource($vehicle))
                    ->response()
                    ->setStatusCode(201);
            } else {
                return response()->json([
                    'error' => 'Parking Lot with id '.$parkingLot->id.' has not tariffs.'
                ])->setStatusCode(405);
            }
        } else {
            return response()->json([
                'error' => 'You cannot create vehicles to customers to parking lots that do not belong to you.'
            ])->setStatusCode(403);
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
        $parkingLot = $person->parking_lot;
        if ($parkingLot->owner_id == $id){
            if($vehicle->person_id == $person->id){
                return (new VehicleResource($vehicle))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json(['error' => 'This vehicle does not belong to that person.'])
                    ->setStatusCode(403);
            }
        } else {
            return response()->json([
                'error' => 'This person does not belong to your parking lot.'
            ])->setStatusCode(403);
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
        return response()->json(['data' => 'Update method is not allowed.'])
            ->setStatusCode(405);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        return response()->json(['data' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
