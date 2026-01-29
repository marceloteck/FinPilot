<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['Confirmado', 'Revisar', 'Pendente'];

        foreach ($statuses as $name) {
            Status::create(['name' => $name]);
        }
    }
}
