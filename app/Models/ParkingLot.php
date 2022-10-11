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
        'owner_id',
    ];

    protected $casts = [
        'rows' => 'integer',
        'columns' => 'integer',
        'owner_id' => 'integer',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class, 'parking_lot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    public static function findByName(string $name)
    {
        try {
            return ParkingLot::where('name', 'LIKE', "%{$name}%")->take(5)->get();
        } catch (Exception $e) {
            return NULL;
        }
    }

    public static function findByOwnerId(string $owner_id)
    {
        try {
            return ParkingLot::where('owner_id', $owner_id)->get();
        } catch (Exception $e) {
            return NULL;
        }
    }

    public static function findByNameOfUser(string $name, int $owner_id)
    {
        try {
            return ParkingLot::where('name', 'LIKE', "%{$name}%")->where('owner_id', $owner_id)->get();
        } catch (Exception $e) {
            return NULL;
        }
    }
}
