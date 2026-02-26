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
    Schema::create('cards', function (Blueprint $table) {
        $table->id();
        // Identificador del juego (magic, pokemon, yugioh, etc)
        $table->string('game')->index(); 
        
        // Datos básicos de la carta
        $table->string('name');
        $table->string('set_name');
        $table->string('set_code')->index();
        $table->string('collector_number')->nullable();
        $table->string('rarity');
        
        // El "Cerebro": Aquí guardaremos los datos específicos de cada juego (Maná, HP, etc.)
        $table->json('attributes')->nullable(); 
        
        // Datos para tu ERP/Tienda
        $table->decimal('price', 10, 2)->default(0.00);
        $table->integer('stock')->default(0);
        $table->string('image_path')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
