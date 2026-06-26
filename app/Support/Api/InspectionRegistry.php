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
                'search_own'           => ['pica_number', 'inspector', 'findings', 'findings_status', 'remarks', 'condition', 'inspection_status'],
                'search_relation'      => 'computer',
                'search_relation_cols' => ['computer_code', 'computer_name', 'number_asset_ho', 'serial_number', 'ip_address', 'location', 'status', 'condition', 'dept'],
                'search_user_relation' => 'computer.pengguna',
                'search_user_cols'     => ['username', 'department', 'nrp', 'position'],
            ],
            'laptop' => [
                'model'           => InspeksiLaptop::class,
                'site_column'     => 'site',
                'relations'       => ['inventory.pengguna'],
                'inventory_model' => InvLaptop::class,
                'inventory_fk'    => 'inv_laptop_id',
                // Server-side global search (web DataTable parity): own columns +
                // the eager-loaded inventory + its pengguna (UserAll).
                'search_own'           => ['pica_number', 'inspector', 'findings', 'findings_status', 'remarks', 'condition', 'inspection_status'],
                'search_relation'      => 'inventory',
                'search_relation_cols' => ['laptop_code', 'laptop_name', 'number_asset_ho', 'serial_number', 'ip_address', 'location', 'status', 'condition', 'dept'],
                'search_user_relation' => 'inventory.pengguna',
                'search_user_cols'     => ['username', 'department', 'nrp', 'position'],
            ],
            'mobile-tower' => [
                'model'           => InspeksiMobileTower::class,
                'site_column'     => 'site',
                'relations'       => ['mt'],
                'inventory_model' => InvMobileTower::class,
                'inventory_fk'    => 'inv_mt_id',
                'search_own'           => ['pica_number', 'inspector', 'findings', 'findings_status', 'remarks', 'condition', 'inspection_status'],
                'search_relation'      => 'mt',
                'search_relation_cols' => ['mt_code', 'inventory_number', 'type_mt', 'location', 'detail_location', 'status', 'condition', 'padlock_code'],
            ],
            'printer' => [
                'model'           => InspeksiPrinter::class,
                'site_column'     => 'site',
                'relations'       => ['printer'],
                'inventory_model' => InvPrinter::class,
                'inventory_fk'    => 'inv_printer_id',
                'search_own'           => ['pica_number', 'inspector', 'findings', 'findings_status', 'remarks', 'condition', 'inspection_status'],
                'search_relation'      => 'printer',
                'search_relation_cols' => ['printer_code', 'item_name', 'asset_ho_number', 'serial_number', 'ip_address', 'printer_brand', 'printer_type', 'department', 'location', 'status'],
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
