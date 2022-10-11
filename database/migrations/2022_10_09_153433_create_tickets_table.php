<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->dateTime('entry_date');
            $table->dateTime('remove_date')->nullable();
            $table->foreignId('parking_spot_id');
            $table->foreignId('vehicle_id');
            $table->timestamps();

            $table->foreign('parking_spot_id')->references('id')->on('parking_spots')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('vehicle_id')->references('id')->on('vehicles')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
