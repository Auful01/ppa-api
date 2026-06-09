<?php

namespace App\Console\Commands;

use App\Models\InspeksiTower;
use App\Models\InvTower;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importInventoryToTinspeksiTower extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiTower:cron';

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
         $inventoriesMobileTower = InvTower::get();

         // Insert data into inspeksi
         foreach ($inventoriesMobileTower as $inventoryMobileTower) {
             InspeksiTower::create([
                 'inv_tower_id' => $inventoryMobileTower->id,
                 'month' => Carbon::now()->format('m'),
                 'year' => Carbon::now()->format('Y'),
             ]);
         }
 
         $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
