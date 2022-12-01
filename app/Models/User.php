<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Database\Factories\UserFactory;

use Exception;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function parkingLots()
    {
        return $this->hasMany(ParkingLot::class, 'owner_id');
    }

    public static function findByUsername(string $username)
    {
        try {
            return User::where('username', $username);
        } catch (Exception $e) {
            return NULL;
        }
    }

    public static function findByEmail(string $email)
    {
        try {
            return User::where('email', $email);
        } catch (Exception $e) {
            return NULL;
        }
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
