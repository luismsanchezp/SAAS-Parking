<?php

namespace App\Models;

use Database\Factories\VehicleTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'tariff',
        'creation_date',
        'parking_lot_id',
    ];

    protected $casts = [
        'tariff' => 'double',
        'parking_lot_id' => 'integer',
    ];

    public function parking_lot()
    {
        return $this->belongsTo(ParkingLot::class, 'parking_lot_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type_id');
    }

    static public function getCurrentTariffByType(string $type) {
        return VehicleType::orderBy('creation_date', 'desc')
            ->where('type', $type)
            ->limit(1)->get()->first();
    }

    protected static function newFactory()
    {
        return VehicleTypeFactory::new();
    }
}
