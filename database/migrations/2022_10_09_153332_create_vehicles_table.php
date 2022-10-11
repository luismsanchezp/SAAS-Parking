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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate', 10)->unique();
            $table->string('color', 10);
            $table->foreignId('person_id');
            $table->foreignId('vehicle_type_id');
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('people')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')
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
        Schema::dropIfExists('vehicles');
    }
};
