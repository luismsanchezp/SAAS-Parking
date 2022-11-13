<?php

namespace Database\Factories;

use App\Models\ParkingSpot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingSpot>
 */
class ParkingSpotFactory extends Factory
{
    protected $model = ParkingSpot::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'row' => fake()->unique(true)->numberBetween(1, 4),
            'column' => 1,
            'parking_lot_id' => \App\Models\ParkingLot::factory(),
        ];
    }
}
