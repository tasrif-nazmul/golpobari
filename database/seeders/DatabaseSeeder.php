<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Installs the starting catalogue. No admin account is created here:
     * run `php artisan golpobari:create-admin` after seeding.
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            PackageSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
