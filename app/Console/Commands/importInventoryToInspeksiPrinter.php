<?php

namespace App\Console\Commands;

use App\Models\InspeksiPrinter;
use App\Models\InvPrinter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importInventoryToInspeksiPrinter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiPrinter:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from inventory to inspeksi printer every month';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        // Retrieve data from inventory
        $inventories_printer = InvPrinter::get();
        $currentDate = Carbon::now();
        // dd($inventories_printer);
        // Insert data into inspeksi
        foreach ($inventories_printer as $inventory_printer) {
            $cek = InspeksiPrinter::where('inv_printer_id', $inventory_printer->id)
                ->where('year', $currentDate->format('Y'))
                ->where('month', $currentDate->format('m'))
                ->first();

            if (!$cek) {
                InspeksiPrinter::create([
                    'inv_printer_id' => $inventory_printer->id,
                    'created_date' => $currentDate->format('Y-m-d H:i:s'),
                    'month' => $currentDate->format('m'),
                    'year' => $currentDate->format('Y'),
                    'inspection_status' => 'N',
                    'inventory_status' => $inventory_printer->status,
                    'created_at' => $currentDate->format('Y-m-d H:i:s'),
                    'site' => $inventory_printer->site
                ]);
            } elseif ($cek->inspection_status === 'N') {
                // Jika sudah ada dan statusnya N → update dari inventory
                $cek->update([
                    'inventory_status' => $inventory_printer->status,
                    'site' => $inventory_printer->site,
                ]);
            }
        }

        $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
