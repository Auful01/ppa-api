<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RootCauseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'GPS',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'PC/LAPTOP',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'PRINTER',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'ADMINISTRASI',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'CCTV',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'TELKOMSEL',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'RADIOHT',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'RADIORIG',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'NETWORK MT',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'NETWORK BUILDING',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'INTERNET',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'FILE SERVER',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'SS6',
            ],
        );

        DB::table('root_cause_categories')->insert(
            [
                'category_root_cause' => 'WEBSITE',
            ],
        );
    }
}
