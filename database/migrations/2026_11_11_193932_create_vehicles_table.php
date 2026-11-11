<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institutional_number', 50)->unique();
            $table->enum('category', ['administrative', 'passenger']);
            $table->string('manufacturer', 100);
            $table->string('model', 100);
            $table->smallInteger('year');
            $table->string('plate_number', 50)->unique();
            $table->string('engine_number', 100)->nullable();
            $table->string('chassis_number', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->integer('current_odometer')->default(0);
            $table->enum('status', ['active', 'under_repair', 'out_of_service', 'retired'])->default('active');
            $table->text('notes')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
