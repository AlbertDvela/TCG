<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ygo_cards', function (Blueprint $table) {
            // Usamos decimal para precisión de moneda (ej: 999.99)
            $table->decimal('market_price', 10, 2)->nullable()->after('rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ygo_cards', function (Blueprint $table) {
            //
        });
    }
};
