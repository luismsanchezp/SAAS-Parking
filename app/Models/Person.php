<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'id_type',
        'gov_id',
        'phone_number',
        'parking_lot_id',
    ];

    protected $casts = [
        'parking_lot_id' => 'integer',
    ];

    public function parking_lot()
    {
        return $this->belongsTo(ParkingLot::class, 'parking_lot_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'person_id');
    }

    protected static function newFactory()
    {
        return PersonFactory::new();
    }
}
