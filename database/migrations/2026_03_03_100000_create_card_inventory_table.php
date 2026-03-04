<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_inventory', function (Blueprint $table) {
            $table->id();

            // ── SKU / Código de barras ────────────────────────────────────────
            // UUID corto alfanumérico de 10 caracteres, único por ítem físico.
            // Se genera automáticamente en el Model (boot).
            $table->string('sku', 10)->unique();

            // ── Relación con el catálogo ──────────────────────────────────────
            // Hereda toda la info de la carta (nombre, imagen, atk/def, etc.)
            // No copiamos campos: si el catálogo se actualiza, el inventario lo refleja.
            $table->foreignId('ygo_card_id')
                  ->constrained('ygo_cards')
                  ->restrictOnDelete(); // No borrar carta si tiene inventario

            // ── Características físicas del ejemplar ─────────────────────────
            // Estos SÍ son propios del ejemplar físico, pueden diferir del catálogo.
            $table->foreignId('card_condition_id')
                  ->constrained('card_conditions');

            $table->foreignId('card_edition_id')
                  ->constrained('card_editions');

            $table->string('language')->default('Español');

            $table->integer('quantity')->default(1);

            // ── Imagen propia del ejemplar ────────────────────────────────────
            // Nullable: si está vacío, el frontend hereda image_path de ygo_cards.
            $table->string('custom_image_path')->nullable();

            // ── Precios ───────────────────────────────────────────────────────
            // price_at_purchase: lo que pagaste para adquirir la carta.
            $table->decimal('price_at_purchase', 10, 2)->nullable();

            // market_price_snapshot: captura del TCGPlayer price al momento del registro.
            // Útil para comparar históricamente cuánto subió/bajó desde que la compraste.
            $table->decimal('market_price_snapshot', 10, 2)->nullable();

            // selling_price: tu precio manual de venta.
            // Si está null, se sugiere usar market_price de ygo_cards en la UI.
            $table->decimal('selling_price', 10, 2)->nullable();

            // ── Auditoría / Trazabilidad ──────────────────────────────────────
            // customer_id: de quién se compró (consignación o compra directa). Nullable.
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            // registered_by: usuario ERP que dio de alta este ítem.
            $table->foreignId('registered_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // ── Notas ─────────────────────────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_inventory');
    }
};
