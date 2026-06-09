<?php

namespace App\Console\Commands;

use App\Models\InspeksiSwitch;
use App\Models\InvSwitch;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportInventoryToInspeksiSwitch extends Command
{
      /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiSwitch:cron';

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
        $inventoriesSwitch = InvSwitch::get();

        // Insert data into inspeksi
        foreach ($inventoriesSwitch as $inventorySwitch) {
            InspeksiSwitch::create([
                'inv_switch_id' => $inventorySwitch->id,
                'ip_address' => $inventorySwitch->ip_address,
                'month' => Carbon::now()->format('m'),
                'year' => Carbon::now()->format('Y'),
            ]);
        }

        $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
