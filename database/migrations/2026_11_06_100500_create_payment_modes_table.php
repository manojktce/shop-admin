<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How money is paid or received; affects_cash_drawer = counts in daily cash
 * (requirements §3.3; database-structure.xlsx → payment_modes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->boolean('affects_cash_drawer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_modes');
    }
};
