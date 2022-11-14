<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ParkingLotFactory;

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

    public function parkingSpots()
    {
        return $this->hasMany(ParkingSpot::class, 'parking_lot_id');
    }

    public function vehicleTypes()
    {
        return $this->hasMany(VehicleType::class, 'parking_lot_id');
    }

    public static function findByOwnerId(string $owner_id)
    {
        try {
            return ParkingLot::where('owner_id', $owner_id)->get();
        } catch (Exception $e) {
            return NULL;
        }
    }

    public static function findByParkingLotName(string $name, int $owner_id)
    {
        try {
            return ParkingLot::where('name', 'LIKE', "%{$name}%")->where('owner_id', $owner_id)->get();
        } catch (Exception $e) {
            return NULL;
        }
    }

        /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ParkingLotFactory::new();
    }
}
