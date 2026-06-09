<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'DAFA BINTANG A',
    //             'nrp' => '23002073',
    //             'password' => Hash::make('23002073'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_developer',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'DIKRI ERDIN A',
    //             'nrp' => '22003193',
    //             'password' => Hash::make('22003193'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_developer',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'NI PUTU DIAN M',
    //             'nrp' => '42231288',
    //             'password' => Hash::make('42231288'),
    //             'position' => 'ICT ADMIN',
    //             'role' => 'ict_admin',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'HARTONI',
    //             'nrp' => '14020631',
    //             'password' => Hash::make('14020631'),
    //             'position' => 'ICT GROUP LEADER',
    //             'role' => 'ict_group_leader',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'ABDUL HAMID',
    //             'nrp' => '20000322',
    //             'password' => Hash::make('20000322'),
    //             'position' => 'ICT GROUP LEADER',
    //             'role' => 'ict_group_leader',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'SLAMET HUDA FIRMANSYAH',
    //             'nrp' => '18125648',
    //             'password' => Hash::make('18125648'),
    //             'position' => 'ICT GROUP LEADER',
    //             'role' => 'ict_group_leader',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'AHMAD MUHAMMAD RIDHO',
    //             'nrp' => '21002037',
    //             'password' => Hash::make('21002037'),
    //             'position' => 'ICT GROUP LEADER',
    //             'role' => 'ict_group_leader',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'M AZMIE RAMADAN',
    //             'nrp' => '20000456',
    //             'password' => Hash::make('20000456'),
    //             'position' => 'ICT GROUP LEADER',
    //             'role' => 'ict_group_leader',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'CHOIRUL ROHMAT HIDAYAT',
    //             'nrp' => '22001578',
    //             'password' => Hash::make('22001578'),
    //             'position' => 'ICT KOORDINATOR',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'RIZQI TAUFIK',
    //             'nrp' => '22002653',
    //             'password' => Hash::make('22002653'),
    //             'position' => 'ICT KOORDINATOR',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'ISMET',
    //             'nrp' => '21001844',
    //             'password' => Hash::make('21001844'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'FERY AQRONA',
    //             'nrp' => '22003279',
    //             'password' => Hash::make('22003279'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'RESTU ADJI SAPUTRA',
    //             'nrp' => '23000993',
    //             'password' => Hash::make('23000993'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'M. RASYID ALI ROFI',
    //             'nrp' => '24002927',
    //             'password' => Hash::make('24002927'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'MUHAMMAD BAGAS NOFIANOR',
    //             'nrp' => '24003981',
    //             'password' => Hash::make('24003981'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'OKY CAHYO RAHMANDIKA',
    //             'nrp' => '22004789',
    //             'password' => Hash::make('22004789'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'FAJAR WICAKSONO',
    //             'nrp' => '22004290',
    //             'password' => Hash::make('22004290'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'ALI MUSTOFA',
    //             'nrp' => '22003372',
    //             'password' => Hash::make('22003372'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'GALIH KHAFIUTAMA',
    //             'nrp' => '22001504',
    //             'password' => Hash::make('22001504'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'MUHAMMAD RIDHO PRATAMA',
    //             'nrp' => '23001708',
    //             'password' => Hash::make('23001708'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'M DANDI AKBAR',
    //             'nrp' => '22004614',
    //             'password' => Hash::make('22004614'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'YUDHI ANSYARI',
    //             'nrp' => '22003275',
    //             'password' => Hash::make('22003275'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'MUHAMMAD MUDJAKIR',
    //             'nrp' => '42231351',
    //             'password' => Hash::make('42231351'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'IBNU JAHID SAFIRUDDIN',
    //             'nrp' => '21001154',
    //             'password' => Hash::make('21001154'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    //     DB::table('users')->insert(
    //         [
    //             'name' => 'DAVID VALENTINUS TAN',
    //             'nrp' => '22004396',
    //             'password' => Hash::make('22004396'),
    //             'position' => 'ICT TECHNICIAN',
    //             'role' => 'ict_technician',
    //         ],
    //     );
    // }

    public function run(): void
    {
        DB::table('users')->insert(
            [
                'name' => 'HARIS FADHILLAH BATU BARA',
                'nrp' => '18054043',
                'password' => Hash::make('18054043'),
                'position' => 'GROUP LEADER',
                'role' => 'ict_group_leader',
                'site' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'EDI SAPUTRA',
                'nrp' => '17092763',
                'password' => Hash::make('17092763'),
                'position' => 'GROUP LEADER',
                'role' => 'ict_group_leader',
                'role' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'IMAM SUPRIADI',
                'nrp' => '22002922',
                'password' => Hash::make('22002922'),
                'position' => 'ict_group_leader',
                'role' => 'ict_group_leader',
                'role' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'AGUS WIDIYANTO',
                'nrp' => '22002488',
                'password' => Hash::make('22002488'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'FEBRI MIFTAQUL RIZA',
                'nrp' => '22003280',
                'password' => Hash::make('22003280'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'YUKO',
                'nrp' => '23000928',
                'password' => Hash::make('23000928'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );
        DB::table('users')->insert(
            [
                'name' => 'TEUKU ALFIN ALSA',
                'nrp' => '24000134',
                'password' => Hash::make('24000134'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );

        DB::table('users')->insert(
            [
                'name' => 'RIZKY BATUBARA',
                'nrp' => '24001109',
                'password' => Hash::make('24001109'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );

        DB::table('users')->insert(
            [
                'name' => 'T ROVID NINA',
                'nrp' => '2401229',
                'password' => Hash::make('2401229'),
                'position' => 'ADMIN COE',
                'role' => 'admin',
                'role' => 'MIFA',
            ],
        );
        
        DB::table('users')->insert(
            [
                'name' => 'KARMILATUL HAYYAT',
                'nrp' => '2401289',
                'password' => Hash::make('2401289'),
                'position' => 'ADMIN COE',
                'role' => 'admin',
                'role' => 'MIFA',
            ],
        );

        DB::table('users')->insert(
            [
                'name' => 'SYAFLIZAR',
                'nrp' => '2401358',
                'password' => Hash::make('2401358'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );

        DB::table('users')->insert(
            [
                'name' => 'PUPUT SYAHPUTRA',
                'nrp' => '2401359',
                'password' => Hash::make('2401359'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );

        DB::table('users')->insert(
            [
                'name' => 'DICKY ALVIAN MAHENDRA',
                'nrp' => '24001948',
                'password' => Hash::make('24001948'),
                'position' => 'ICT TECHNICIAN',
                'role' => 'ict_technician',
                'role' => 'MIFA',
            ],
        );
    }
}
