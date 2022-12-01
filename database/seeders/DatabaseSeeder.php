<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\api\v1\ParkingLotController;
use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use App\Models\Person;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->createUser('vienna');
        $this->createUser('mike');
    }

    private function createUser(string $username) {
        \App\Models\User::factory()
            ->has(ParkingLot::factory()->count(2)
                ->has(Person::factory()->count(2), 'persons')
                , 'parkingLots')
            ->create([
                'username' => $username,
                'email' => $username.'@gmail.com',
            ]);
        $user = \App\Models\User::findByUsername($username)->get()->first();
        $parkingLots = $user->parkingLots;
        foreach ($parkingLots as $pL) {
            for($r = 1; $r <= $pL->rows; $r++){
                for($c = 1; $c <= $pL->columns; $c++){
                    ParkingSpot::create([
                        'row'=>$r,
                        'column'=>$c,
                        'parking_lot_id'=>$pL->id
                    ]);
                }
            }
            VehicleType::factory(1)->create(['type' => 'Car', 'parking_lot_id' => $pL->id]);
            VehicleType::factory(1)->create(['type' => 'Motorbike', 'parking_lot_id' => $pL->id]);
            $people = $pL->persons;
            $vTs = $pL->vehicleTypes;
            foreach ($people as $person) {
                foreach ($vTs as $vt) {
                    Vehicle::factory()->count(2)->create([
                        'person_id' => $person->id,
                        'vehicle_type_id' => $vt->id
                    ]);
                }
            }
        }
    }
}
