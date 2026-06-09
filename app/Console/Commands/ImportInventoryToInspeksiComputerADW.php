<?php

namespace App\Console\Commands;

use App\Models\InspeksiComputer;
use App\Models\InvComputer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportInventoryToInspeksiComputerADW extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiComputerAdw:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from inventory to inspeksi computers every monthly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         // Retrieve data from inventory
         $inventories_computer = InvComputer::where('site', 'ADW')->get();

         foreach ($inventories_computer as $inventory_computer) {
 
             $triwulan = Carbon::now()->quarter;
             $month = Carbon::now()->month;
             $year = Carbon::now()->year;
 
             $cek = InspeksiComputer::where('inv_computer_id',  $inventory_computer->id)
             ->where('month', $month)
             ->where('year', $year)
             ->first();
 
             if (!$cek) {
                 InspeksiComputer::create([
                     'inv_computer_id' => $inventory_computer->id,
                     'created_date' => Carbon::now()->format('Y-m-d H:i:s'),
                     'triwulan' => $triwulan,
                     'month' => Carbon::now()->format('m'),
                     'year' => Carbon::now()->format('Y'),
                     'inspection_status' => 'N',
                     'inventory_status' => $inventory_computer->status,
                     'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                     'site' => $inventory_computer->site
                 ]);
             } elseif ($cek->inspection_status === 'N') {
                 // Jika sudah ada dan statusnya N → update dari inventory
                 $cek->update([
                     'inventory_status' => $inventory_computer->status,
                     'site' => $inventory_computer->site,
                 ]);
             }
         }
 
         $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
