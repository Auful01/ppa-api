<?php

namespace App\Support\Api;

use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\InvAp;
use App\Models\InvCctv;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvMobileTower;
use App\Models\InvPrinter;
use App\Models\InvScanner;
use App\Models\InvSwitch;
use App\Models\InvWirelless;

class InventoryRegistry
{
    public static function all(): array
    {
        return [
            'access-point' => [
                'model' => InvAp::class,
                'site_column' => 'site',
                'code_column' => 'inventory_number',
                'name_column' => 'device_name',
                'relations' => [],
                'inspection_model' => null,
                'complaint_column' => 'inventory_number',
            ],
            'cctv' => [
                'model' => InvCctv::class,
                'site_column' => 'site',
                'code_column' => 'cctv_code',
                'name_column' => 'cctv_name',
                'relations' => ['switch'],
                'inspection_model' => null,
                'complaint_column' => 'cctv_code',
            ],
            'computer' => [
                'model' => InvComputer::class,
                'site_column' => 'site',
                'code_column' => 'computer_code',
                'name_column' => 'computer_name',
                'relations' => ['pengguna'],
                'inspection_model' => InspeksiComputer::class,
                'inspection_foreign_key' => 'inv_computer_id',
                'complaint_column' => 'computer_code',
            ],
            'laptop' => [
                'model' => InvLaptop::class,
                'site_column' => 'site',
                'code_column' => 'laptop_code',
                'name_column' => 'laptop_name',
                'relations' => ['pengguna'],
                'inspection_model' => InspeksiLaptop::class,
                'inspection_foreign_key' => 'inv_laptop_id',
                'complaint_column' => 'laptop_code',
            ],
            'mobile-tower' => [
                'model' => InvMobileTower::class,
                'site_column' => 'site',
                'code_column' => 'mt_code',
                'name_column' => 'type_mt',
                'relations' => [],
                'inspection_model' => InspeksiMobileTower::class,
                'inspection_foreign_key' => 'inv_mt_id',
                'complaint_column' => 'inventory_number',
            ],
            'printer' => [
                'model' => InvPrinter::class,
                'site_column' => 'site',
                'code_column' => 'printer_code',
                'name_column' => 'item_name',
                'relations' => [],
                'inspection_model' => InspeksiPrinter::class,
                'inspection_foreign_key' => 'inv_printer_id',
                'complaint_column' => 'printer_code',
                // live_schema: printer_code NOT NULL — must be supplied on create
                'required_fields' => ['printer_code'],
            ],
            'scanner' => [
                'model' => InvScanner::class,
                'site_column' => 'site',
                'code_column' => 'scanner_code',
                'name_column' => 'item_name',
                'relations' => [],
                'inspection_model' => null,
                'complaint_column' => 'scanner_code',
            ],
            'switch' => [
                'model' => InvSwitch::class,
                'site_column' => 'site',
                'code_column' => 'inventory_number',
                'name_column' => 'device_name',
                'relations' => [],
                'inspection_model' => null,
                'complaint_column' => 'inventory_number',
            ],
            'wireless' => [
                'model' => InvWirelless::class,
                'site_column' => 'site',
                'code_column' => 'inventory_number',
                'name_column' => 'device_name',
                'relations' => [],
                'inspection_model' => null,
                'complaint_column' => 'inventory_number',
            ],
        ];
    }

    public static function get(string $type): array
    {
        $config = self::all()[strtolower($type)] ?? null;

        abort_if($config === null, 404, 'Inventory type not supported.');

        return $config;
    }
}
