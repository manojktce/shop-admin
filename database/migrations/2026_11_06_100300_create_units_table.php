<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Units of measure: 1 unit = conversion_factor × base unit; base_unit_id is NULL for a base unit
 * (requirements §3.3; database-structure.xlsx → units).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('short_name', 10)->unique();
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_factor', 12, 6)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
