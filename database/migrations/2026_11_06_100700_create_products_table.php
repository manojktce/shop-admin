<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items sold; stock kept in the base unit (requirements §4.1; database-structure.xlsx → products).
 * current_stock is a cache of SUM(stock_movements) maintained by the Stock module.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150)->index();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('base_unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('tax_id')->constrained('taxes')->restrictOnDelete();
            $table->decimal('selling_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->boolean('allow_decimal_qty')->default(false);
            $table->decimal('min_stock', 12, 3)->default(0);
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
