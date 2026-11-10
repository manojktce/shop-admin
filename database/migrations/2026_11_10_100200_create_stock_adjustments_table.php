<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual stock corrections; base_qty is signed (requirements §4.2; database-structure.xlsx → stock_adjustments).
 * reason is validated against StockAdjustment::REASONS (string column, as elsewhere in this project).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->date('adjustment_date')->index();
            $table->decimal('base_qty', 12, 3);
            $table->string('reason', 30);
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
