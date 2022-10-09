<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rows',
        'columns',
    ];

    protected $casts = [
        'rows' => 'integer',
        'columns' => 'integer',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class, 'parking_lot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashiers()
    {
        return $this->hasMany(Cashier::class, 'parking_lot_id');
    }

    public function parking_spots()
    {
        return $this->hasMany(ParkingSpot::class, 'parking_lot_id');
    }

    public function vehicle_types()
    {
        return $this->hasMany(VehicleType::class, 'parking_lot_id');
    }
}
