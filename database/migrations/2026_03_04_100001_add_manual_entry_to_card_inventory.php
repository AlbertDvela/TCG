<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {

            // ygo_card_id pasa a ser nullable: ya no es obligatorio si se ingresa manualmente
            $table->foreignId('ygo_card_id')
                  ->nullable()
                  ->change();

            // ── Campos para ingreso manual (cuando la carta no está en el catálogo) ──
            // Se guardan en la misma tabla. Si ygo_card_id está presente, estos se ignoran.
            $table->string('manual_name')->nullable()->after('ygo_card_id');
            $table->string('manual_set_code')->nullable()->after('manual_name');
            $table->string('manual_rarity')->nullable()->after('manual_set_code');
            $table->string('manual_type')->nullable()->after('manual_rarity');
            $table->string('manual_attribute')->nullable()->after('manual_type');
            $table->integer('manual_atk')->nullable()->after('manual_attribute');
            $table->integer('manual_def')->nullable()->after('manual_atk');
            $table->text('manual_description')->nullable()->after('manual_def');
            $table->string('manual_image_url')->nullable()->after('manual_description');

            // Flag que indica si el registro fue ingresado manualmente
            $table->boolean('is_manual_entry')->default(false)->after('manual_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('card_inventory', function (Blueprint $table) {
            $table->dropColumn([
                'manual_name', 'manual_set_code', 'manual_rarity',
                'manual_type', 'manual_attribute', 'manual_atk',
                'manual_def', 'manual_description', 'manual_image_url',
                'is_manual_entry',
            ]);

            $table->foreignId('ygo_card_id')
                  ->nullable(false)
                  ->change();
        });
    }
};
