<?php

namespace Database\Seeders;

use App\Models\Kandang;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ========== CREATE KANDANG ==========
        $kandang1 = Kandang::create([
            'nama_kandang' => 'Kandang 1',
            'jumlah_ayam' => 0,
            'keterangan' => '',
            'status' => 'aktif',
        ]);

        $kandang2 = Kandang::create([
            'nama_kandang' => 'Kandang 2',
            'jumlah_ayam' => 0,
            'keterangan' => '',
            'status' => 'aktif',
        ]);

        $kandang3 = Kandang::create([
            'nama_kandang' => 'Kandang 3',
            'jumlah_ayam' => 0,
            'keterangan' => '',
            'status' => 'aktif',
        ]);

        // ========== CREATE USERS ==========
        // 👑 Pemilik (Owner)
        $pemilik = User::create([
            'name' => 'Pemilik',
            'username' => 'pemilik',
            'email' => 'pemilik@hansjaya.com',
            'password' => bcrypt('password'),
            'role' => 'pemilik',
            'kandang_id' => null,
            'email_verified_at' => now(),
        ]);
        $pemilik->assignRole('pemilik');

        // 🐔 Kandang 1 Worker
        $pekerja1 = User::create([
            'name' => 'Kandang 1',
            'username' => 'kandang1',
            'email' => 'kandang1@hansjaya.com',
            'password' => bcrypt('password'),
            'role' => 'pekerja',
            'kandang_id' => $kandang1->id,
            'email_verified_at' => now(),
        ]);
        $pekerja1->assignRole('pekerja');

        // 🐔 Kandang 2 Worker
        $pekerja2 = User::create([
            'name' => 'Kandang 2',
            'username' => 'kandang2',
            'email' => 'kandang2@hansjaya.com',
            'password' => bcrypt('password'),
            'role' => 'pekerja',
            'kandang_id' => $kandang2->id,
            'email_verified_at' => now(),
        ]);
        $pekerja2->assignRole('pekerja');

        // 🐔 Kandang 3 Worker
        $pekerja3 = User::create([
            'name' => 'Kandang 3',
            'username' => 'kandang3',
            'email' => 'kandang3@hansjaya.com',
            'password' => bcrypt('password'),
            'role' => 'pekerja',
            'kandang_id' => $kandang3->id,
            'email_verified_at' => now(),
        ]);
        $pekerja3->assignRole('pekerja');

        echo "✓ Database seeding complete!\n";
        echo "✓ 3 Kandang created\n";
        echo "✓ 1 Pemilik user created\n";
        echo "✓ 3 Pekerja users created (kandang1, kandang2, kandang3)\n";
    }
}
