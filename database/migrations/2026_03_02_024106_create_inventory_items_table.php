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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            // Esto crea 'inventoriable_id' e 'inventoriable_type' automáticamente
            $table->morphs('inventoriable'); 
            
            $table->string('condition'); // NM, LP, etc.
            $table->boolean('is_foil')->default(false);
            $table->string('language')->default('Spanish');
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_purchase', 10, 2)->nullable();
            $table->decimal('current_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
