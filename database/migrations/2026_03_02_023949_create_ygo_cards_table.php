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
        Schema::create('ygo_cards', function (Blueprint $table) {
            $table->id();
            $table->string('konami_id')->unique()->nullable(); // ID oficial de la carta
            $table->string('name');
            $table->string('set_code'); // Ej: LOB-001
            $table->string('rarity');
            $table->enum('edition', ['1st Edition', 'Unlimited', 'Limited', 'Promo']);
            $table->string('attribute')->nullable(); // DARK, LIGHT...
            $table->integer('level')->nullable();
            $table->string('type')->nullable(); // Dragon / Fusion / Effect
            $table->integer('atk')->nullable();
            $table->integer('def')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ygo_cards');
    }
};
