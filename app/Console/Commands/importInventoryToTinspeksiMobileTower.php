<?php

namespace App\Console\Commands;

use App\Models\InspeksiMobileTower;
use App\Models\InvMobileTower;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importInventoryToTinspeksiMobileTower extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiMobileTower:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve data from inventory
        $inventories_mt = InvMobileTower::get();
        $currentDate = Carbon::now();
        // dd($inventories_mt);
        // Insert data into inspeksi
        foreach ($inventories_mt as $inventory_mt) {
            $cek = InspeksiMobileTower::where('inv_mt_id', $inventory_mt->id)
                ->where('year', $currentDate->format('Y'))
                ->where('month', $currentDate->format('m'))
                ->first();

            if (!$cek) {
                InspeksiMobileTower::create([
                    'inv_mt_id' => $inventory_mt->id,
                    'created_date' => $currentDate->format('Y-m-d H:i:s'),
                    'month' => $currentDate->format('m'),
                    'year' => $currentDate->format('Y'),
                    'inspection_status' => 'N',
                    'created_at' => $currentDate->format('Y-m-d H:i:s'),
                    'site' => $inventory_mt->site
                ]);
            } elseif ($cek->inspection_status === 'N') {
                // Jika sudah ada dan statusnya N → update dari inventory
                $cek->update([
                    'site' => $inventory_mt->site,
                ]);
            }
        }

        $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
