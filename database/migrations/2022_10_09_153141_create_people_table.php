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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name', 25);
            $table->string('surname', 25);
            $table->enum('id_type', ['C.C.', 'C.E.', 'T.I.']);
            $table->string('gov_id', 10)->unique();
            $table->string('phone_number', 10)->unique();
            $table->foreignId('parking_lot_id');
            $table->timestamps();

            $table->foreign('parking_lot_id')->references('id')->on('parking_lots')
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
        Schema::dropIfExists('people');
    }
};
