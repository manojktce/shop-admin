<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-tap quantity buttons per product for billing / kiosk; qty is in the given unit
 * (requirements §4.1; database-structure.xlsx → product_quick_qtys).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_quick_qtys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('qty', 12, 3);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_quick_qtys');
    }
};
