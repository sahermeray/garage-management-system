<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete()->restrictOnUpdate();
            $table->date('repair_date');
            $table->integer('odometer')->nullable();
            $table->text('problem');
            $table->text('description');
            $table->enum('location', ['institution', 'external'])->default('institution');
            $table->string('workshop_name', 150)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('repair_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
