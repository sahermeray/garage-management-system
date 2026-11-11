<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trip_number', 20)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('driver_id')->constrained('drivers')->restrictOnDelete()->restrictOnUpdate();
            $table->date('trip_date');
            $table->string('destination', 200);
            $table->text('purpose');
            $table->time('departure_time')->nullable();
            $table->time('return_time')->nullable();
            $table->integer('odometer_start');
            $table->integer('odometer_end')->nullable();
            $table->integer('distance')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('driver_id');
            $table->index('trip_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
