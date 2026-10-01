<?php

namespace Database\Seeders;

use App\Support\Rbac\SystemRbac;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(SystemRbac::class)->ensure();
    }
}
