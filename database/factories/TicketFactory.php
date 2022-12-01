<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class TicketFactory extends Factory
{

    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'entry_date' => Carbon::now()->toDateTimeString(),
            'remove_date' => date('Y-m-d H:i:s', Carbon::now()->timestamp+400000),
            'parking_spot_id' => 1,
            'vehicle_id' => \App\Models\Vehicle::factory(),
        ];
    }
}
