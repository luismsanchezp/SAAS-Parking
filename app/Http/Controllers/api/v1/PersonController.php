<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\Person;
use Illuminate\Http\Request;

use App\Http\Requests\api\v1\PersonStoreRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PersonResource;

class PersonController extends Controller
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
            if ($request->exists('govid'))
            {
                return $this->findByGovId($parkingLot->id, $request->input('govid'));
            } else {
                $people = Person::where('parking_lot_id', $parkingLot->id)->get();
                return response()->json([
                    'data' => PersonResource::collection($people)
                ], 200);
            }
        } else {
            return response()->json(['error' => 'You do not own this parking lot.'])
                ->setStatusCode(403);
        }
    }

    private function findByGovId(int $parkingLotId, string $gov_id){
        $person = Person::where('gov_id', $gov_id)->get()->first();
        if ($person == NULL) {
            return response()->json(
                ['error' => 'Customer not found.']
            )->setStatusCode(404);
        } else if ($person->parking_lot_id != $parkingLotId) {
            return response()->json([
                'error' => 'Customer does not belong to this parking lot.'
            ])->setStatusCode(404);
        } else {
            return (new PersonResource($person
                ->loadMissing('parking_lot')
                ->loadMissing('vehicles')))
                ->response()
                ->setStatusCode(200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PersonStoreRequest $request, ParkingLot $parkingLot)
    {
        $id = Auth::user()->id;
        if ($parkingLot->owner_id == $id){
            $name = $request->input('name');
            $surname = $request->input('surname');
            $id_type = $request->input('id_type');
            $gov_id = $request->input('gov_id');
            $phone_number = $request->input('phone_number');
            $person = Person::create([
                'name'=>$name,
                'surname'=>$surname,
                'id_type'=>$id_type,
                'gov_id'=>$gov_id,
                'phone_number'=>$phone_number,
                'parking_lot_id'=>$parkingLot->id
            ]);
            return (new PersonResource($person
                ->loadMissing('parking_lot')))
                ->response()
                ->setStatusCode(201);
        } else {
            return response()->json([
                'error' => 'You cannot create customers to parking lots that do not belong to you.'
            ])->setStatusCode(403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function show(ParkingLot $parkingLot, Person $person)
    {
        $user_id = Auth::user()->id;
        if ($parkingLot->owner_id == $user_id){
            if ($person->parking_lot_id == $parkingLot->id){
                return (new PersonResource($person
                    ->loadMissing('parking_lot')
                    ->loadMissing('vehicles')))
                    ->response()
                    ->setStatusCode(200);
            } else {
                return response()->json([
                    'error' => 'This parking lot with ID '.$parkingLot->id.' does not belong to that person.'
                ], 406);
            }
        } else {
            return response()->json(['error' => 'You do not own this parking lot.'], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Person $person)
    {
        return response()->json(['error' => 'Update method is not allowed.'])
            ->setStatusCode(405);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function destroy(Person $person)
    {
        return response()->json(['error' => 'Delete method is not allowed.'])
            ->setStatusCode(405);
    }
}
