<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory(1)
            ->has(ParkingLot::factory(1)
                ->has(ParkingSpot::factory(4), 'parkingSpots')
                ->has(Person::factory()
                    ->has(Vehicle::factory(), 'vehicles'), 'persons')
                ->has(VehicleType::factory(1), 'vehicleTypes'), 'parkingLots')
            ->create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
        ]);

        \App\Models\User::factory(2)
            ->has(ParkingLot::factory(1)
                ->has(ParkingSpot::factory(4), 'parkingSpots')
                ->has(Person::factory()
                    ->has(Vehicle::factory(), 'vehicles'), 'persons')
                ->has(VehicleType::factory(1), 'vehicleTypes'), 'parkingLots')
            ->create();
    }
}
