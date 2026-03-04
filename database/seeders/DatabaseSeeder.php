<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario administrador por defecto
        User::factory()->create([
            'name'  => 'Admin YuGi House',
            'email' => 'admin@yugihouse.com',
        ]);

        // Catálogos base necesarios para el inventario
        $this->call([
            CardConditionSeeder::class,
            CardEditionSeeder::class,
            ProductTypeSeeder::class,
        ]);
    }
}
