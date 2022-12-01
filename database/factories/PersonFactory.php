<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'id_type' => 'C.C.',
            'gov_id' => fake()->unique()->randomNumber($nbDigits=7, true),
            'phone_number' => fake()->unique()->randomNumber($nbDigits=7, true),
            'parking_lot_id' => \App\Models\ParkingLot::factory(),
        ];
    }
}
