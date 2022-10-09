<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'user_id',
        'parking_lot_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'parking_lot_id' => 'integer',
    ];

    public function parking_lot()
    {
        return $this->belongsTo(ParkingLot::class, 'parking_lot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'cashier_id');
    }
}
