<?php

namespace App\Http\Controllers\api\v1;

use App\Enums\VehicleTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\VehicleTypeResource;
use App\Http\Requests\api\v1\VehicleTypeStoreRequest;

use Carbon\Carbon;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ParkingLot $parkingLot, Request $request)
    {
        if ($request->exists('current')) {
            $car = VehicleType::getCurrentTariffByType(VehicleTypeEnum::car->value);
            $motorbike = VehicleType::getCurrentTariffByType(VehicleTypeEnum::motorbike->value);
            return response()->json([
                'data' => VehicleTypeResource::collection([
                    'car' => $car,
                    'motorbike' => $motorbike
                ])
            ], 200);
        } else {
            $vehicleTypes = VehicleType::where('parking_lot_id', $parkingLot->id)->get();
            return response()->json(['data' => VehicleTypeResource::collection($vehicleTypes)], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ParkingLot $parkingLot, VehicleTypeStoreRequest $request)
    {
        $id = Auth::user()->id;
        if ($parkingLot->owner_id == $id){
            $type = $request->input('type');
            $tariff = $request->input('tariff');
            $creation_date = Carbon::now()->toDateTimeString();
            $vehicleType = VehicleType::create([
                'type' => $type,
                'tariff' => $tariff,
                'creation_date' => $creation_date,
                'parking_lot_id' => $parkingLot->id
            ]);
            return (new VehicleTypeResource($vehicleType))
                ->response()
                ->setStatusCode(201);
        } else {
            return response()->json([
                'data' => 'You cannot create vehicle types to parking lots that do not belong to you.'
            ])->setStatusCode(403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingLot $parkingLot, VehicleType $vehicleType)
    {

        if ($vehicleType->parking_lot_id == $parkingLot->id){
            return (new VehicleTypeResource($vehicleType))
                ->response()
                ->setStatusCode(200);
        } else {
            return response()->json([
                'message'=>'This parking lot with ID '.$parkingLot->id.' does not belong to that vehicle type.'
            ], 406);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        return response()->json(['data' => 'Update method is not allowed.'])
            ->setStatusCode(405);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleType $vehicleType)
    {
        return response()->json(['data' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
