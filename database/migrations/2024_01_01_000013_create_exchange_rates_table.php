<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pair', 7);  // e.g. NGN/USD
            $table->decimal('buy_rate', 15, 4);
            $table->decimal('sell_rate', 15, 4);
            $table->decimal('mid_rate', 15, 4);
            $table->date('effective_date');
            $table->timestamps();

            $table->unique(['pair', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
