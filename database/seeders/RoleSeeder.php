<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'pemilik', 'guard_name' => 'web'],
            ['name' => 'pemilik', 'guard_name' => 'web']
        );

        Role::firstOrCreate(
            ['name' => 'pekerja', 'guard_name' => 'web'],
            ['name' => 'pekerja', 'guard_name' => 'web']
        );
    }
}
