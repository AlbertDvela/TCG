<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            [
                'code'        => 'NM',
                'description' => 'Near Mint — Sin marcas visibles. Condición perfecta o casi perfecta.',
            ],
            [
                'code'        => 'LP',
                'description' => 'Lightly Played — Desgaste mínimo: pequeñas rozaduras o bordes levemente marcados.',
            ],
            [
                'code'        => 'MP',
                'description' => 'Moderately Played — Desgaste moderado visible: dobleces leves, rozaduras notables.',
            ],
            [
                'code'        => 'HP',
                'description' => 'Heavily Played — Desgaste fuerte: dobleces, marcas de juego, posibles rasgaduras menores.',
            ],
            [
                'code'        => 'DMG',
                'description' => 'Damaged — Carta dañada: rasgaduras, perforaciones, escritura, dobleces severos.',
            ],
        ];

        foreach ($conditions as $condition) {
            DB::table('card_conditions')->updateOrInsert(
                ['code' => $condition['code']],
                array_merge($condition, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ CardConditions insertadas: NM, LP, MP, HP, DMG');
    }
}
