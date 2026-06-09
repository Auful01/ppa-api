<?php

namespace App\Console\Commands;

use App\Models\InspeksiAccessPoint;
use App\Models\InvAp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importInventoryToInspeksiAp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiAp:cron';

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
        $inventoriesAp = InvAp::get();

        // Insert data into inspeksi
        foreach ($inventoriesAp as $inventoryAp) {
            InspeksiAccessPoint::create([
                'inv_ap_id' => $inventoryAp->id,
                'ip_address' => $inventoryAp->ip_address,
                'month' => Carbon::now()->format('m'),
                'year' => Carbon::now()->format('Y'),
            ]);
        }

        $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
