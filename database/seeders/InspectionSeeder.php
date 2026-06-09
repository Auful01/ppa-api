<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPanelBoxNetworkInspections();
        $this->seedComputerInspections();
        $this->seedLaptopInspections();
        $this->seedPrinterInspections();
        $this->seedAccessPointInspections();
        $this->seedWirellessInspections();
        $this->seedSwitchInspections();
        $this->seedMobileTowerInspections();
        $this->seedTowerInspections();
        $this->seedBaInspectionTables();
    }

    private function seedPanelBoxNetworkInspections(): void
    {
        DB::table('inspeksi_panel_box_networks')->where('pica_number', 'like', 'SEED-PBN-%')->delete();

        $this->insertRows('inspeksi_panel_box_networks', [
            $this->withTimestamps([
                'pica_number' => 'SEED-PBN-001',
                'created_date' => '2026-01-15',
                'month' => 1,
                'year' => 2026,
                'inspection_status' => 'sudah_inspeksi',
                'findings' => 'Kabel patch belum rapi',
                'findings_action' => 'Rapikan jalur kabel dan pasang label ulang',
                'findings_status' => 'open',
                'due_date' => '2026-01-25',
                'cleanliness' => 'Baik',
                'conditions' => 'Perlu Perbaikan',
                'remarks' => 'Area panel box perlu dirapikan',
                'cable_arrangement' => 'Kurang Rapi',
                'inspection_by' => 'Seeder Inspector',
                'inspection_at' => '2026-01-15 09:00:00',
                'approvred_by' => 'Seeder Head',
                'status_approval' => 'pending',
            ]),
            $this->withTimestamps([
                'pica_number' => 'SEED-PBN-002',
                'created_date' => '2026-02-16',
                'month' => 2,
                'year' => 2026,
                'inspection_status' => 'sudah_inspeksi',
                'findings' => 'Tidak ada temuan',
                'findings_status' => 'closed',
                'cleanliness' => 'Baik',
                'conditions' => 'Baik',
                'remarks' => 'Panel box normal',
                'cable_arrangement' => 'Rapi',
                'inspection_by' => 'Seeder Inspector',
                'inspection_at' => '2026-02-16 10:00:00',
                'approvred_by' => 'Seeder Head',
                'status_approval' => 'approve',
            ]),
            $this->withTimestamps([
                'pica_number' => 'SEED-PBN-003',
                'created_date' => '2026-03-17',
                'month' => 3,
                'year' => 2026,
                'inspection_status' => 'belum_inspeksi',
                'findings_status' => 'open',
                'due_date' => '2026-03-27',
                'cleanliness' => 'Cukup',
                'conditions' => 'Belum Dicek',
                'remarks' => 'Menunggu jadwal inspeksi',
                'cable_arrangement' => 'Belum Dicek',
                'inspection_by' => 'Seeder Inspector',
                'status_approval' => 'pending',
            ]),
        ]);
    }

    private function seedComputerInspections(): void
    {
        DB::table('inspeksi_computers')->where('pica_number', 'like', 'SEED-CMP-%')->delete();

        $this->insertRows('inspeksi_computers', [
            $this->withTimestamps($this->computerRow('SEED-CMP-001', 1, 'Y', 'Baik', 'closed')),
            $this->withTimestamps($this->computerRow('SEED-CMP-002', 2, 'Y', 'Perlu Perbaikan', 'open')),
            $this->withTimestamps($this->computerRow('SEED-CMP-003', 3, 'N', 'Belum Dicek', 'open')),
        ]);
    }

    private function seedLaptopInspections(): void
    {
        DB::table('inspeksi_laptops')->where('pica_number', 'like', 'SEED-LTP-%')->delete();

        $this->insertRows('inspeksi_laptops', [
            $this->withTimestamps($this->laptopRow('SEED-LTP-001', 'Baik', 'Y', 'closed')),
            $this->withTimestamps($this->laptopRow('SEED-LTP-002', 'Perlu Perbaikan', 'Y', 'open')),
            $this->withTimestamps($this->laptopRow('SEED-LTP-003', 'Belum Dicek', 'N', 'open')),
        ]);
    }

    private function seedPrinterInspections(): void
    {
        DB::table('inspeksi_printers')->where('pica_number', 'like', 'SEED-PRN-%')->delete();

        $this->insertRows('inspeksi_printers', [
            $this->withTimestamps($this->printerRow('SEED-PRN-001', 1, 'Baik', 'Y', 'closed')),
            $this->withTimestamps($this->printerRow('SEED-PRN-002', 2, 'Perlu Perbaikan', 'Y', 'open')),
            $this->withTimestamps($this->printerRow('SEED-PRN-003', 3, 'Belum Dicek', 'N', 'open')),
        ]);
    }

    private function seedAccessPointInspections(): void
    {
        $this->seedNetworkDeviceInspections('inspeksi_access_points', 'SEED-AP', 'inv_ap_id');
    }

    private function seedWirellessInspections(): void
    {
        $this->seedNetworkDeviceInspections('inspeksi_wirellesses', 'SEED-WRL', 'inv_wirelless_id');
    }

    private function seedSwitchInspections(): void
    {
        $this->seedNetworkDeviceInspections('inspeksi_switches', 'SEED-SW', 'inv_switch_id');
    }

    private function seedMobileTowerInspections(): void
    {
        DB::table('inspeksi_mobile_towers')->where('pica_number', 'like', 'SEED-MT-%')->delete();

        $this->insertRows('inspeksi_mobile_towers', [
            $this->withTimestamps($this->mobileTowerRow('SEED-MT-001', 1, 'Baik', 'sudah_inspeksi', 'closed')),
            $this->withTimestamps($this->mobileTowerRow('SEED-MT-002', 2, 'Perlu Perbaikan', 'sudah_inspeksi', 'open')),
            $this->withTimestamps($this->mobileTowerRow('SEED-MT-003', 3, 'Belum Dicek', 'belum_inspeksi', 'open')),
        ]);
    }

    private function seedTowerInspections(): void
    {
        DB::table('inspeksi_towers')->where('pica_number', 'like', 'SEED-TWR-%')->delete();

        $this->insertRows('inspeksi_towers', [
            $this->withTimestamps($this->towerRow('SEED-TWR-001', 1, 'Baik', 'sudah_inspeksi', 'closed')),
            $this->withTimestamps($this->towerRow('SEED-TWR-002', 2, 'Perlu Perbaikan', 'sudah_inspeksi', 'open')),
            $this->withTimestamps($this->towerRow('SEED-TWR-003', 3, 'Belum Dicek', 'belum_inspeksi', 'open')),
        ]);
    }

    private function seedBaInspectionTables(): void
    {
        foreach (['inspeksi_laptop_bas', 'inspeksi_computer_bas'] as $table) {
            DB::table($table)->whereIn('created_at', [
                '2026-01-15 08:00:00',
                '2026-02-16 08:00:00',
                '2026-03-17 08:00:00',
            ])->delete();

            $this->insertRows($table, [
                ['created_at' => '2026-01-15 08:00:00', 'updated_at' => '2026-01-15 08:00:00'],
                ['created_at' => '2026-02-16 08:00:00', 'updated_at' => '2026-02-16 08:00:00'],
                ['created_at' => '2026-03-17 08:00:00', 'updated_at' => '2026-03-17 08:00:00'],
            ]);
        }
    }

    private function seedNetworkDeviceInspections(string $table, string $prefix, string $foreignKey): void
    {
        DB::table($table)->where('pica_number', 'like', $prefix . '-%')->delete();

        $this->insertRows($table, [
            $this->withTimestamps($this->networkDeviceRow($prefix . '-001', $foreignKey, 1, 'Baik', 'sudah_inspeksi', 'closed')),
            $this->withTimestamps($this->networkDeviceRow($prefix . '-002', $foreignKey, 2, 'Perlu Perbaikan', 'sudah_inspeksi', 'open')),
            $this->withTimestamps($this->networkDeviceRow($prefix . '-003', $foreignKey, 3, 'Belum Dicek', 'belum_inspeksi', 'open')),
        ]);
    }

    private function computerRow(string $picaNumber, int $month, string $inspectionStatus, string $condition, string $findingStatus): array
    {
        return [
            'pica_number' => $picaNumber,
            'created_date' => sprintf('2026-%02d-15', $month),
            'month' => $month,
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'triwulan' => 'TW-' . ceil($month / 3),
            'inspector' => 'Seeder Inspector',
            'conditions' => $condition,
            'physique_condition_cpu' => $condition,
            'physique_condition_internal_cpu' => $condition,
            'physique_condition_monitor' => $condition,
            'software_license' => 'Valid',
            'software_standaritation' => 'Sesuai',
            'software_device_name_standaritation' => 'Sesuai',
            'software_clear_cache' => 'Done',
            'software_system_restore' => 'Active',
            'software_windows_update' => 'Updated',
            'software_storage_health' => 'Good',
            'defrag' => 'Done',
            'hard_maintenance' => 'Done',
            'security_change_password' => 'Done',
            'security_auto_lock' => 'Active',
            'security_input_password' => 'Active',
            'crew' => 'ICT Support',
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Perlu pengecekan lanjutan',
            'findings_action' => $findingStatus === 'closed' ? null : 'Lakukan follow up oleh teknisi',
            'findings_status' => $findingStatus,
            'inspection_image' => 'seed/inspection-computer.jpg',
            'remarks' => 'Data sample inspeksi komputer',
            'due_date' => sprintf('2026-%02d-25', $month),
            'inventory_status' => 'active',
            'ip_address' => '10.10.' . $month . '.11',
            'location' => 'Office ' . $month,
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approved' : 'pending',
            'site' => 'MIFA',
            'last_edited_by' => 'Seeder',
        ];
    }

    private function laptopRow(string $picaNumber, string $condition, string $inspectionStatus, string $findingStatus): array
    {
        return [
            'pica_number' => $picaNumber,
            'created_date' => '2026-01-15',
            'condition' => $condition,
            'inspection_at' => '2026-01-15 09:30:00',
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'inspector' => 'Seeder Inspector',
            'software_defrag' => 'Done',
            'software_check_system_restore' => 'Active',
            'software_clean_cache_data' => 'Done',
            'software_check_ilegal_software' => 'Clear',
            'software_change_password' => 'Done',
            'software_windows_license' => 'Valid',
            'software_office_license' => 'Valid',
            'software_standaritation_software' => 'Sesuai',
            'software_update_sinology' => 'Updated',
            'software_turn_off_windows_update' => 'Done',
            'software_cheking_ssd_health' => 'Done',
            'software_percentage_ssd_health' => '96%',
            'software_standaritation_device_name' => 'Sesuai',
            'hardware_fan_cleaning' => 'Done',
            'hardware_change_pasta' => 'Done',
            'hardware_any_maintenance' => 'No',
            'hardware_any_maintenance_explain' => null,
            'security_change_password' => 'Done',
            'security_auto_lock' => 'Active',
            'security_input_password' => 'Active',
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Battery health perlu dipantau',
            'findings_action' => $findingStatus === 'closed' ? null : 'Monitoring battery health',
            'findings_status' => $findingStatus,
            'inspection_image' => 'seed/inspection-laptop.jpg',
            'remarks' => 'Data sample inspeksi laptop',
            'due_date' => '2026-01-25',
            'inventory_status' => 'active',
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approved' : 'pending',
            'site' => 'MIFA',
            'last_edited_by' => 'Seeder',
        ];
    }

    private function printerRow(string $picaNumber, int $month, string $condition, string $inspectionStatus, string $findingStatus): array
    {
        return [
            'pica_number' => $picaNumber,
            'created_date' => sprintf('2026-%02d-15', $month),
            'condition' => $condition,
            'inspection_at' => sprintf('2026-%02d-15 11:00:00', $month),
            'month' => $month,
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'inspector' => 'Seeder Inspector',
            'tinta_cyan' => '80%',
            'tinta_magenta' => '75%',
            'tinta_yellow' => '70%',
            'tinta_black' => '85%',
            'body_condition' => $condition,
            'usb_cable_condition' => 'Baik',
            'power_cable_condition' => 'Baik',
            'performing_physical_power_cleaning' => 'Done',
            'performing_cleaning_on_the_printer_waste_box' => 'Done',
            'performing_cleaning_head' => 'Done',
            'performing_print_quality_test' => 'Done',
            'do_replacing_cable' => 'No',
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Hasil nozzle kurang rata',
            'findings_action' => $findingStatus === 'closed' ? null : 'Cleaning head ulang',
            'findings_status' => $findingStatus,
            'remarks' => 'Data sample inspeksi printer',
            'due_date' => sprintf('2026-%02d-25', $month),
            'inventory_status' => 'active',
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approved' : 'pending',
            'inspection_image' => 'seed/inspection-printer.jpg',
            'nozle_image' => 'seed/nozzle-printer.jpg',
            'site' => 'MIFA',
            'last_edited_by' => 'Seeder',
        ];
    }

    private function networkDeviceRow(string $picaNumber, string $foreignKey, int $month, string $condition, string $inspectionStatus, string $findingStatus): array
    {
        return [
            $foreignKey => null,
            'pica_number' => $picaNumber,
            'ip_address' => '10.20.' . $month . '.10',
            'created_date' => sprintf('2026-%02d-15', $month),
            'condition' => $condition,
            'inspection_at' => sprintf('2026-%02d-15 13:00:00', $month),
            'month' => $month,
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'device_status' => $inspectionStatus === 'belum_inspeksi' ? 'unchecked' : 'online',
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Signal perlu dicek ulang',
            'findings_status' => $findingStatus,
            'findings_action' => $findingStatus === 'closed' ? null : 'Optimasi posisi perangkat',
            'due_date' => sprintf('2026-%02d-25', $month),
            'remarks' => 'Data sample inspeksi perangkat network',
            'inspector' => 'Seeder Inspector',
            'scrap' => 'No',
            'scrap_note' => null,
            'inventory_status' => 'active',
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approve' : 'pending',
        ];
    }

    private function mobileTowerRow(string $picaNumber, int $month, string $condition, string $inspectionStatus, string $findingStatus): array
    {
        return [
            'pica_number' => $picaNumber,
            'created_date' => sprintf('2026-%02d-15', $month),
            'worthiness' => $condition === 'Baik' ? 'Layak' : 'Perlu Review',
            'condition' => $condition,
            'inspection_at' => sprintf('2026-%02d-15 14:00:00', $month),
            'month' => $month,
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'device_status' => $inspectionStatus === 'belum_inspeksi' ? 'unchecked' : 'online',
            'physic_condition_mobile_tower' => $condition,
            'physic_condition_mobile_tower_text' => 'Sample kondisi fisik mobile tower',
            'battery_circuit' => $condition,
            'battery_circuit_text' => 'Tegangan battery dalam batas sample',
            'solar_panel' => $condition,
            'solar_panel_text' => 'Solar panel bersih',
            'device_circuit_output' => $condition,
            'device_circuit_output_text' => 'Output perangkat normal',
            'checklist_results_list' => json_encode(['battery' => $condition, 'solar_panel' => $condition]),
            'list_results_remark' => json_encode(['remark' => 'Data sample checklist mobile tower']),
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Koneksi intermittent',
            'findings_status' => $findingStatus,
            'findings_action' => $findingStatus === 'closed' ? null : 'Cek grounding dan power output',
            'inspection_image' => 'seed/inspection-mobile-tower.jpg',
            'due_date' => sprintf('2026-%02d-25', $month),
            'remarks' => 'Data sample inspeksi mobile tower',
            'inspector' => 'Seeder Inspector',
            'pic' => 'Seeder PIC',
            'crew' => 'ICT Support',
            'list_of_needs' => $findingStatus === 'closed' ? null : 'Cable ties, label, multimeter',
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approve' : 'pending',
            'site' => 'MIFA',
        ];
    }

    private function towerRow(string $picaNumber, int $month, string $condition, string $inspectionStatus, string $findingStatus): array
    {
        return [
            'pica_number' => $picaNumber,
            'created_date' => sprintf('2026-%02d-15', $month),
            'worthiness' => $condition === 'Baik' ? 'Layak' : 'Perlu Review',
            'condition' => $condition,
            'inspection_at' => sprintf('2026-%02d-15 15:00:00', $month),
            'month' => $month,
            'year' => 2026,
            'inspection_status' => $inspectionStatus,
            'device_status' => $inspectionStatus === 'belum_inspeksi' ? 'unchecked' : 'online',
            'physic_condition_tower' => $condition,
            'physic_condition_tower_text' => 'Sample kondisi fisik tower',
            'grounding_tower' => $condition,
            'grounding_tower_text' => 'Grounding sample masih aman',
            'fence_tower' => $condition,
            'fence_tower_text' => 'Pagar tower sample',
            'findings' => $findingStatus === 'closed' ? 'Tidak ada temuan' : 'Pagar perlu pengecatan',
            'findings_status' => $findingStatus,
            'findings_action' => $findingStatus === 'closed' ? null : 'Jadwalkan pengecatan pagar',
            'due_date' => sprintf('2026-%02d-25', $month),
            'remarks' => 'Data sample inspeksi tower',
            'inspector' => 'Seeder Inspector',
            'pic' => 'Seeder PIC',
            'crew' => 'ICT Support',
            'list_of_needs' => $findingStatus === 'closed' ? null : 'Cat, kuas, thinner',
            'approved_by' => 'Seeder Head',
            'status_approval' => $findingStatus === 'closed' ? 'approve' : 'pending',
        ];
    }

    private function withTimestamps(array $row): array
    {
        return array_merge($row, [
            'created_at' => '2026-01-15 08:00:00',
            'updated_at' => '2026-01-15 08:00:00',
        ]);
    }

    private function insertRows(string $table, array $rows): void
    {
        $columns = [];

        foreach ($rows as $row) {
            $columns = array_unique(array_merge($columns, array_keys($row)));
        }

        $normalizedRows = array_map(function (array $row) use ($columns) {
            return array_merge(array_fill_keys($columns, null), $row);
        }, $rows);

        DB::table($table)->insert($normalizedRows);
    }
}
