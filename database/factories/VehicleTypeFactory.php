<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VehicleTypeFactory extends Factory
{

    protected $model = VehicleType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'type' => 'Car',
            'tariff' => fake()->randomFloat(2,0, 10000),
            'creation_date' => now(),
            'parking_lot_id' => \App\Models\ParkingLot::factory(),
        ];
    }
}
