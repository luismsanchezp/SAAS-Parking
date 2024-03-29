<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//User routes
Route::get('v1/users',
    [App\Http\Controllers\api\v1\UserController::class,'index'])->name('users.index');
Route::get('v1/users/{user}',
    [App\Http\Controllers\api\v1\UserController::class,'show'])->name('users.show');
Route::post('v1/users',
    [App\Http\Controllers\api\v1\UserController::class,'store'])->name('users.store');

//ParkingLot routes
Route::get('v1/users/{user}/parkinglots',
    [App\Http\Controllers\api\v1\ParkingLotController::class,'index'])->name('parkinglots.index');
Route::get('v1/users/{user}/parkinglots/{parkingLot}',
    [App\Http\Controllers\api\v1\ParkingLotController::class,'show'])->name('parkinglots.show');

//VehicleType routes
Route::get('v1/parkinglots/{parkingLot}/vehicletypes',
    [App\Http\Controllers\api\v1\VehicleTypeController::class,'index'])->name('vehicletypes.index');
Route::get('v1/parkinglots/{parkingLot}/vehicletypes/{vehicleType}',
    [App\Http\Controllers\api\v1\VehicleTypeController::class,'show'])->name('vehicletypes.show');

Route::post('/v1/login',
    [App\Http\Controllers\api\v1\AuthController::class,
        'login'])->name('api.login');

Route::middleware(['auth:sanctum'])->group(function() {
    Route::post('/v1/logout',
        [App\Http\Controllers\api\v1\AuthController::class,
            'logout'])->name('api.logout');

    //User routes
    Route::put('v1/users/{user}',
        [App\Http\Controllers\api\v1\UserController::class,'update'])->name('users.update');

    //Person routes
    Route::get('v1/parkinglots/{parkingLot}/persons',
        [App\Http\Controllers\api\v1\PersonController::class,'index'])->name('persons.index');
    Route::get('v1/parkinglots/{parkingLot}/persons/{person}',
        [App\Http\Controllers\api\v1\PersonController::class,'show'])->name('persons.show');
    Route::post('v1/parkinglots/{parkingLot}/persons',
        [App\Http\Controllers\api\v1\PersonController::class,'store'])->name('persons.store');
    Route::put('v1/parkinglots/{parkingLot}/persons/{person}',
        [App\Http\Controllers\api\v1\PersonController::class,'update'])->name('persons.update');

    //ParkingLot routes
    Route::post('v1/users/{user}/parkinglots',
        [App\Http\Controllers\api\v1\ParkingLotController::class,'store'])->name('parkinglots.store');
    Route::put('v1/users/{user}/parkinglots/{parkingLot}',
        [App\Http\Controllers\api\v1\ParkingLotController::class,'update'])->name('parkinglots.update');

    //Parking Spot routes
    Route::get('v1/parkinglots/{parkingLot}/parkingspots',
        [App\Http\Controllers\api\v1\ParkingSpotController::class,'index'])->name('parkingspots.index');
    Route::get('v1/parkinglots/{parkingLot}/parkingspots/{parkingSpot}',
        [App\Http\Controllers\api\v1\ParkingSpotController::class,'show'])->name('parkingspots.show');

    //Ticket routes
    Route::get('v1/vehicles/{vehicle}/tickets',
        [App\Http\Controllers\api\v1\TicketController::class,'index'])->name('tickets.index');
    Route::get('v1/vehicles/{vehicle}/tickets/{ticket}',
        [App\Http\Controllers\api\v1\TicketController::class,'show'])->name('tickets.show');
    Route::post('v1/vehicles/{vehicle}/tickets',
        [App\Http\Controllers\api\v1\TicketController::class,'store'])->name('tickets.store');
    Route::put('v1/vehicles/{vehicle}/tickets/{ticket}',
        [App\Http\Controllers\api\v1\TicketController::class,'update'])->name('tickets.update');

    //Vehicle routes
    Route::get('v1/parkinglots/{parkingLot}/vehicles',
        [App\Http\Controllers\api\v1\VehicleController::class,'getVehicleByLicensePlate'])->name('vehicles.getVehicleByLicensePlate');
    Route::get('v1/persons/{person}/vehicles',
        [App\Http\Controllers\api\v1\VehicleController::class,'index'])->name('vehicles.index');
    Route::get('v1/persons/{person}/vehicles/{vehicle}',
        [App\Http\Controllers\api\v1\VehicleController::class,'show'])->name('vehicles.show');
    Route::post('v1/persons/{person}/vehicles',
        [App\Http\Controllers\api\v1\VehicleController::class,'store'])->name('vehicles.store');

    //VehicleType routes
    Route::post('v1/parkinglots/{parkingLot}/vehicletypes',
        [App\Http\Controllers\api\v1\VehicleTypeController::class,'store'])->name('vehicletypes.store');
});
