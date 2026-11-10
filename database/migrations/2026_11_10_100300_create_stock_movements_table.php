<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock ledger: one signed row per stock change, with running balance
 * (requirements §4.2; database-structure.xlsx → stock_movements). products.current_stock caches the latest balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->dateTime('movement_date');
            $table->string('type', 20);
            $table->decimal('base_qty', 12, 3);
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('balance_after', 12, 3);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
