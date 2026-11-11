<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('repair_id')->constrained('repairs')->cascadeOnDelete();
            $table->string('description', 200);
            $table->enum('type', ['spare_part', 'labor', 'service', 'other']);
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->index('repair_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_items');
    }
};
