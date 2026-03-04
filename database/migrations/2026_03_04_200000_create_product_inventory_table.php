<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_inventory', function (Blueprint $table) {
            $table->id();

            // SKU único por lote de producto
            $table->string('sku', 10)->unique();

            // Relación con el catálogo de productos
            $table->foreignId('ygo_product_id')
                  ->constrained('ygo_products')
                  ->restrictOnDelete();

            // Estado del producto sellado
            $table->enum('condition', ['Sellado', 'Abierto', 'Dañado'])
                  ->default('Sellado');

            $table->string('language')->default('Español');

            $table->integer('quantity')->default(1);

            // Imagen propia del lote (opcional, hereda de ygo_products)
            $table->string('custom_image_path')->nullable();

            // Precios
            $table->decimal('price_at_purchase', 10, 2)->nullable();
            $table->decimal('market_price_snapshot', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();

            // Trazabilidad
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            $table->foreignId('registered_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventory');
    }
};
