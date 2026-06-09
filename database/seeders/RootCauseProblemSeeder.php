<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RootCauseProblemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '1',
                'root_cause_problem' => 'KARTU GPS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '1',
                'root_cause_problem' => 'PERBAIKAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '1',
                'root_cause_problem' => 'PELEPASAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '1',
                'root_cause_problem' => 'PEMASANGAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'PEMINDAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'RAM',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'MONITOR',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'LCD',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'KABEL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'OS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'DRIVER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'HARDISK',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'APLIKASI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'INSPEKSI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'POWER SUPPLY',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'MOTHERBOARD',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'UPS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'KEYBOARD',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '2',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'TINTA HABIS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'TINTA BOCOR',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'INSTALL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'PEMINDAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'PERLU RESET',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'SENSOR',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'INSPEKSI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'PERBAIKAN HASIL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '3',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'PENGAJUAN IKK',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'PERPANJANGAN IKK',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'PENGAJUAN WASKAT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'PERPANJANGAN WASKAT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'INVOICE HALO',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '4',
                'root_cause_problem' => 'PENGAJUAN KARTU HALO',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'PEMASANGAN BARU',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'PEMINDAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'BACKBONE',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'CAMERA',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'NVR',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'SWITCH',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'POWER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'PERGANTIAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'PELEPASAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '5',
                'root_cause_problem' => 'MAINTENANCE',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '6',
                'root_cause_problem' => 'TIDAK TERCOVER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '6',
                'root_cause_problem' => 'SINYAL LAMBAT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '6',
                'root_cause_problem' => 'KARTU HALO PROBLEM',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '6',
                'root_cause_problem' => 'INSPEKSI TOWER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '6',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'PENAMBAHAN CHANNEL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'SORTING CHANNEL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'POWER RADIO',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'KABEL ANTENA',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'ANTENA HILANG',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'ANTENA BELUM MATCH',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'INSTALASI POWER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'PERGANTIAN ANTENA',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'PENGECEKAN MODULASI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '7',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'PENAMBAHAN CHANNEL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'SORTING CHANNEL',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'POWER RADIO',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'KABEL ANTENA',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'ANTENA HILANG',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'ANTENA BELUM MATCH',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'INSTALASI POWER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'PERGANTIAN ANTENA',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'PENGECEKAN MODULASI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '8',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'LINK BACKBONE',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'PEMINDAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'MPPT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'BATERAI',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'KABEL LAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'KABEL POWER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'INSPEKSI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '9',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'PENAMBAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'PEMINDAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'LINK BACKBONE',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'FO CUT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'MPPT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'BATERAI',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'AP PROBLEM',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '10',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '11',
                'root_cause_problem' => 'INTERNET DOWN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '11',
                'root_cause_problem' => 'AKSES LAMBAT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '11',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '12',
                'root_cause_problem' => 'PENAMBAHAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '12',
                'root_cause_problem' => 'RECONNECT',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '12',
                'root_cause_problem' => 'STORAGE',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '12',
                'root_cause_problem' => 'FILE TERHAPUS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '12',
                'root_cause_problem' => 'PERMINTAAN AKSESS',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '13',
                'root_cause_problem' => 'SERVER',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '13',
                'root_cause_problem' => 'JARINGAN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '13',
                'root_cause_problem' => 'HP KARYAWAN',
            ],
        );

        
        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '13',
                'root_cause_problem' => 'LAIN-LAIN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '14',
                'root_cause_problem' => 'TERBLOKIR',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '14',
                'root_cause_problem' => 'DOWN',
            ],
        );

        DB::table('root_cause_problems')->insert(
            [
                'category_root_cause_id' => '14',
                'root_cause_problem' => 'AKSES LAMBAT',
            ],
        );
    }
}
