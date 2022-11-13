<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{

    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'license_plate' => Str::random(10),
            'color' => fake()->colorName(),
            'person_id' => \App\Models\Person::factory(),
            'vehicle_type_id' => \App\Models\VehicleType::factory(),
        ];
    }
}
