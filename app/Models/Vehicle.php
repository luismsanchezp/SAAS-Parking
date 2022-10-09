<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'color',
        'person_id',
        'vehicle_type_id',
    ];

    protected $casts = [
        'person_id' => 'integer',
        'vehicle_type_id' => 'integer',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function vehicle_type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'vehicle_id');
    }
}
