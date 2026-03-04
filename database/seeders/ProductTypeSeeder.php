<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code'        => 'BOOSTER',
                'description' => 'Booster Pack — Sobre de cartas estándar.',
                'image_url'   => null,
            ],
            [
                'code'        => 'STRUCTURE',
                'description' => 'Structure Deck — Mazo pre-construido temático.',
                'image_url'   => null,
            ],
            [
                'code'        => 'STARTER',
                'description' => 'Starter Deck — Mazo de inicio para nuevos jugadores.',
                'image_url'   => null,
            ],
            [
                'code'        => 'TIN',
                'description' => 'Tin — Lata coleccionable con cartas exclusivas.',
                'image_url'   => null,
            ],
            [
                'code'        => 'MEGA_TIN',
                'description' => 'Mega Tin — Lata grande con contenido ampliado.',
                'image_url'   => null,
            ],
            [
                'code'        => 'BUNDLE',
                'description' => 'Bundle / Collection Box — Caja especial con varios productos.',
                'image_url'   => null,
            ],
            [
                'code'        => 'PROMO_PACK',
                'description' => 'Promo Pack — Pack de cartas promocionales.',
                'image_url'   => null,
            ],
            [
                'code'        => 'ACCESSORY',
                'description' => 'Accesorio — Sleeves, playmats, deck boxes, dados, etc.',
                'image_url'   => null,
            ],
        ];

        foreach ($types as $type) {
            DB::table('product_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ ProductTypes insertados: BOOSTER, STRUCTURE, STARTER, TIN, MEGA_TIN, BUNDLE, PROMO_PACK, ACCESSORY');
    }
}
