<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DebtSeeder;
use Database\Seeders\DebtTypeSeeder;
use Database\Seeders\StatusSeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\ViewSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            StatusSeeder::class,
            TransactionSeeder::class,
            ViewSeeder::class,
            DebtTypeSeeder::class,
            DebtSeeder::class,
        ]);
    }
}
