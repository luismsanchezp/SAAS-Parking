<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ParkingLot;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParkingLot>
 */
class ParkingLotFactory extends Factory
{

    protected $model = ParkingLot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'rows' => 4,
            'columns' => 1,
            'owner_id' => \App\Models\User::factory(),
        ];
    }
}
