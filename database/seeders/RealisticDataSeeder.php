<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kandang;
use App\Models\Pengaturan;
use App\Models\HargaTelur;
use App\Models\ProduksiTelur;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan semua data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProduksiTelur::truncate();
        DetailPenjualan::truncate();
        Penjualan::truncate();
        HargaTelur::truncate();
        Kandang::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buat roles
        Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        Role::create(['name' => 'pekerja', 'guard_name' => 'web']);

        // Set konversi
        Pengaturan::updateOrCreate(
            ['kunci' => 'konversi_butir_per_kg'],
            ['nilai' => 16]
        );

        // Buat akun pemilik
        $pemilik = User::create([
            'name' => 'Pemilik Hans Jaya',
            'username' => 'pemilik',
            'email' => 'pemilik@hansja.com',
            'password' => bcrypt('password'),
        ]);

        // Buat users
        $user1 = User::create([
            'name' => 'Admin Kandang 1',
            'username' => 'kandang1',
            'email' => 'kandang1@hansja.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'Admin Kandang 2',
            'username' => 'kandang2',
            'email' => 'kandang2@hansja.com',
            'password' => bcrypt('password'),
        ]);

        $user3 = User::create([
            'name' => 'Admin Kandang 3',
            'username' => 'kandang3',
            'email' => 'kandang3@hansja.com',
            'password' => bcrypt('password'),
        ]);

        // Assign roles
        $pemilik->assignRole('pemilik');
        $user1->assignRole('pekerja');
        $user2->assignRole('pekerja');
        $user3->assignRole('pekerja');

        // Buat 3 kandang
        $kandang1 = Kandang::create([
            'nama_kandang' => 'Kandang A',
            'jumlah_ayam' => 2000,
            'status' => 'aktif',
            'keterangan' => 'Kandang utama A',
        ]);

        $kandang2 = Kandang::create([
            'nama_kandang' => 'Kandang B',
            'jumlah_ayam' => 2000,
            'status' => 'aktif',
            'keterangan' => 'Kandang utama B',
        ]);

        $kandang3 = Kandang::create([
            'nama_kandang' => 'Kandang C',
            'jumlah_ayam' => 2000,
            'status' => 'aktif',
            'keterangan' => 'Kandang utama C',
        ]);

        $kandangs = [$kandang1, $kandang2, $kandang3];
        $users = [$user1, $user2, $user3];

        // Harga dengan 3 periode
        // Periode 1: 1 Jan - 20 Jan (Harga dasar)
        $harga1_periode1 = [
            HargaTelur::create([
                'jenis_harga' => 'kandang',
                'harga_per_kg' => 25000,
                'harga_per_butir' => 1562.50,
                'tanggal_berlaku' => Carbon::parse('2026-01-01'),
                'tanggal_akhir' => Carbon::parse('2026-01-20'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga normal periode 1',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'grosir',
                'harga_per_kg' => 26000,
                'harga_per_butir' => 1625,
                'tanggal_berlaku' => Carbon::parse('2026-01-01'),
                'tanggal_akhir' => Carbon::parse('2026-01-20'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga grosir periode 1',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'konsumen',
                'harga_per_kg' => 28000,
                'harga_per_butir' => 1750,
                'tanggal_berlaku' => Carbon::parse('2026-01-01'),
                'tanggal_akhir' => Carbon::parse('2026-01-20'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga konsumen periode 1',
            ]),
        ];

        // Periode 2: 21 Jan - 10 Feb (Harga naik sedikit)
        $harga1_periode2 = [
            HargaTelur::create([
                'jenis_harga' => 'kandang',
                'harga_per_kg' => 25500,
                'harga_per_butir' => 1593.75,
                'tanggal_berlaku' => Carbon::parse('2026-01-21'),
                'tanggal_akhir' => Carbon::parse('2026-02-10'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga naik periode 2',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'grosir',
                'harga_per_kg' => 26500,
                'harga_per_butir' => 1656.25,
                'tanggal_berlaku' => Carbon::parse('2026-01-21'),
                'tanggal_akhir' => Carbon::parse('2026-02-10'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga grosir periode 2',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'konsumen',
                'harga_per_kg' => 28500,
                'harga_per_butir' => 1781.25,
                'tanggal_berlaku' => Carbon::parse('2026-01-21'),
                'tanggal_akhir' => Carbon::parse('2026-02-10'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga konsumen periode 2',
            ]),
        ];

        // Periode 3: 11 Feb - 1 Mar (Harga turun)
        $harga1_periode3 = [
            HargaTelur::create([
                'jenis_harga' => 'kandang',
                'harga_per_kg' => 24000,
                'harga_per_butir' => 1500,
                'tanggal_berlaku' => Carbon::parse('2026-02-11'),
                'tanggal_akhir' => Carbon::parse('2026-03-01'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga turun periode 3',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'grosir',
                'harga_per_kg' => 25000,
                'harga_per_butir' => 1562.50,
                'tanggal_berlaku' => Carbon::parse('2026-02-11'),
                'tanggal_akhir' => Carbon::parse('2026-03-01'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga grosir periode 3',
            ]),
            HargaTelur::create([
                'jenis_harga' => 'konsumen',
                'harga_per_kg' => 27000,
                'harga_per_butir' => 1687.50,
                'tanggal_berlaku' => Carbon::parse('2026-02-11'),
                'tanggal_akhir' => Carbon::parse('2026-03-01'),
                'status' => 'aktif',
                'user_id' => $user1->id,
                'keterangan' => 'Harga konsumen periode 3',
            ]),
        ];

        // Simpan semua harga
        $allHargas = array_merge($harga1_periode1, $harga1_periode2, $harga1_periode3);

        // Data produksi dan penjualan untuk setiap hari
        $startDate = Carbon::parse('2026-01-01');
        $endDate = Carbon::parse('2026-03-01');
        $currentDate = $startDate->copy();
        $stokPerKandang = []; // Track stok harian

        // Initialize stok
        foreach ($kandangs as $k) {
            $stokPerKandang[$k->id] = 0;
        }

        $produksiCount = 1;
        $penjualanCount = 1;

        while ($currentDate <= $endDate) {
            // Untuk setiap kandang
            foreach ($kandangs as $idx => $kandang) {
                // ===== PRODUKSI TELUR =====
                // HDP 88-92% bervariasi
                $hdp = rand(88, 92);
                $jumlahBurirHarian = (int) (2000 * ($hdp / 100)); // Sekitar 1760-1840 butir
                $jumlahKg = round($jumlahBurirHarian / 16, 3);

                // HHP (House Hold Production) - slightly lower than HDP due to mortality
                $hhp = $hdp - rand(0, 3);
                
                // Mortality rate 0.5-2% daily
                $mortalityRate = rand(5, 20) / 10; // 0.5 - 2.0%
                $ayamMati = (int) (2000 * ($mortalityRate / 100));

                ProduksiTelur::create([
                    'kandang_id' => $kandang->id,
                    'user_id' => $users[$idx]->id,
                    'tanggal_produksi' => $currentDate,
                    'satuan_input' => 'butir',
                    'jumlah_input' => $jumlahBurirHarian,
                    'jumlah_butir' => $jumlahBurirHarian,
                    'jumlah_kg' => $jumlahKg,
                    'ayam_mati' => $ayamMati,
                    'hdp' => $hdp,
                    'hhp' => $hhp,
                    'mortality' => $mortalityRate,
                    'keterangan' => "Produksi harian kandang {$kandang->nama_kandang}",
                ]);

                // Update stok dengan produksi hari ini
                $stokPerKandang[$kandang->id] += $jumlahBurirHarian;

                // ===== PENJUALAN =====
                // Tentukan harga yang berlaku hari ini
                $hargaBerlaku = collect($allHargas)->filter(function ($h) use ($currentDate) {
                    return $h->tanggal_berlaku <= $currentDate && $h->tanggal_akhir >= $currentDate;
                });

                if ($hargaBerlaku->count() > 0) {
                    // Jual acak, tapi jangan sampai minus stok
                    $maxJual = $stokPerKandang[$kandang->id];
                    if ($maxJual > 100) {
                        // Jual 30-70% dari stok
                        $jumlahJual = rand((int)($maxJual * 0.3), (int)($maxJual * 0.7));

                        // Tentukan tipe penjualan (kandang/grosir/konsumen secara acak)
                        $tipeJenis = ['kandang', 'grosir', 'konsumen'];
                        $jenisRandom = $tipeJenis[array_rand($tipeJenis)];

                        $hargaItem = $hargaBerlaku->firstWhere('jenis_harga', $jenisRandom);
                        if ($hargaItem) {
                            $jumlahKgJual = round($jumlahJual / 16, 3);
                            $subtotalJual = $jumlahKgJual * $hargaItem->harga_per_kg;

                            $penjualan = Penjualan::create([
                                'tanggal_jual' => $currentDate,
                                'nama_pembeli' => $this->generateBuyerName($jenisRandom),
                                'total_harga' => $subtotalJual,
                                'user_id' => $users[$idx]->id,
                                'keterangan' => "Penjualan dari {$kandang->nama_kandang}"
                            ]);

                            DetailPenjualan::create([
                                'penjualan_id' => $penjualan->id,
                                'harga_telur_id' => $hargaItem->id,
                                'satuan_jual' => 'kg',
                                'jumlah_jual' => $jumlahKgJual,
                                'jumlah_butir' => $jumlahJual,
                                'jumlah_kg' => $jumlahKgJual,
                                'harga_satuan' => $hargaItem->harga_per_kg,
                                'subtotal' => $subtotalJual,
                                'harga_per_kg_saat_jual' => $hargaItem->harga_per_kg,
                                'harga_per_butir_saat_jual' => $hargaItem->harga_per_butir,
                            ]);

                            // Kurangi stok
                            $stokPerKandang[$kandang->id] -= $jumlahJual;
                        }
                    }
                }
            }

            // Lanjut ke hari berikutnya
            $currentDate->addDay();
        }

        $this->command->info('Realistic data seeded successfully!');
        $this->command->info('3 kandang × 60 hari produksi dan penjualan created');
    }

    private function generateBuyerName($type): string
    {
        $names = [
            'kandang' => ['Warung Makan Jaya', 'Toko Telur A', 'Restoran Padang', 'Warung Soto'],
            'grosir' => ['Distributor Telur Jaya', 'CV Telur Untung', 'PT Agro Nasional', 'Koperasi Pangan'],
            'konsumen' => ['Ibu Siti', 'Bapak Ahmad', 'Keluarga Humaedi', 'Mama Wati', 'Pak Hendra', 'Bu Norma'],
        ];

        $nameList = $names[$type] ?? $names['konsumen'];
        return $nameList[array_rand($nameList)];
    }
}
