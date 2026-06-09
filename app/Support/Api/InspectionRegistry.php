<?php

namespace App\Support\Api;

use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvMobileTower;
use App\Models\InvPrinter;

class InspectionRegistry
{
    public static function all(): array
    {
        return [
            'computer' => [
                'model'           => InspeksiComputer::class,
                'site_column'     => 'site',
                'relations'       => ['computer.pengguna'],
                'inventory_model' => InvComputer::class,
                'inventory_fk'    => 'inv_computer_id',
            ],
            'laptop' => [
                'model'           => InspeksiLaptop::class,
                'site_column'     => 'site',
                'relations'       => ['inventory.pengguna'],
                'inventory_model' => InvLaptop::class,
                'inventory_fk'    => 'inv_laptop_id',
            ],
            'mobile-tower' => [
                'model'           => InspeksiMobileTower::class,
                'site_column'     => 'site',
                'relations'       => ['mt'],
                'inventory_model' => InvMobileTower::class,
                'inventory_fk'    => 'inv_mt_id',
            ],
            'printer' => [
                'model'           => InspeksiPrinter::class,
                'site_column'     => 'site',
                'relations'       => ['printer'],
                'inventory_model' => InvPrinter::class,
                'inventory_fk'    => 'inv_printer_id',
            ],
        ];
    }

    public static function get(string $type): array
    {
        $config = self::all()[strtolower($type)] ?? null;

        abort_if($config === null, 404, 'Inspection type not supported.');

        return $config;
    }
}
