<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'remove_date',
        'parking_spot_id',
        'vehicle_id',
    ];

    protected $casts = [
        'parking_spot_id' => 'integer',
        'vehicle_id' => 'integer',
    ];

    public function parking_spot()
    {
        return $this->belongsTo(ParkingSpot::class, 'parking_spot_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
