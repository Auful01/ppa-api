<?php

namespace App\Console\Commands;

use App\Models\InspeksiWirelless;
use App\Models\InvWirelless;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportInventoryToInspeksiWirelless extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspeksiWirelless:cron';

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
        $inventoriesWirelless = InvWirelless::get();

        // Insert data into inspeksi
        foreach ($inventoriesWirelless as $inventoryWirelless) {
            InspeksiWirelless::create([
                'inv_wirelless_id' => $inventoryWirelless->id,
                'ip_address' => $inventoryWirelless->ip_address,
                'month' => Carbon::now()->format('m'),
                'year' => Carbon::now()->format('Y'),
            ]);
        }

        $this->info('Data successfully imported from inventory to inspeksi.');
    }
}
