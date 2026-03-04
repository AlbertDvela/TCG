<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardEditionSeeder extends Seeder
{
    public function run(): void
    {
        $editions = [
            [
                'code'        => '1ST',
                'description' => '1st Edition — Primera edición. Tiene el sello "1st Edition" impreso en la carta.',
            ],
            [
                'code'        => 'UNL',
                'description' => 'Unlimited — Edición ilimitada. Sin sello de edición. La más común.',
            ],
            [
                'code'        => 'LTD',
                'description' => 'Limited Edition — Tirada limitada, generalmente cartas de torneo o promocionales.',
            ],
            [
                'code'        => 'PROMO',
                'description' => 'Promo — Carta promocional de evento, torneo, revista o bundle especial.',
            ],
            [
                'code'        => 'DUEL',
                'description' => 'Duel Terminal — Impresa para las máquinas Duel Terminal. Textura y acabado distinto.',
            ],
            [
                'code'        => 'REPRINT',
                'description' => 'Reprint — Reimpresión en set posterior. Misma carta, diferente set de origen.',
            ],
        ];

        foreach ($editions as $edition) {
            DB::table('card_editions')->updateOrInsert(
                ['code' => $edition['code']],
                array_merge($edition, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ CardEditions insertadas: 1ST, UNL, LTD, PROMO, DUEL, REPRINT');
    }
}
