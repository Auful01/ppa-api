<?php

// use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('inspeksiTower:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
// Schedule::command('sync:user_all')->everyTenMinutes(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
// Schedule::command('sync:user_all')->everyMinute(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //

Schedule::command('inspeksiMobileTower:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiTower:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiComputer:cron')->quarterly(); //  * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1 //
// Schedule::command('inspeksiComputer:cron')->everyMinute(); //  * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiComputerAdw:cron')->monthly(); //  * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1 //
// Schedule::command('inspeksiLaptop:cron')->everyMinute(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiLaptop:cron')->yearly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
// Schedule::command('inspeksiPrinter:cron')->everyMinute();
Schedule::command('inspeksiPrinter:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiAp:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiWirelless:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //
Schedule::command('inspeksiSwitch:cron')->monthly(); // * * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1 //


// ===== add schedule inspection ===========
// Schedule::command('schedulePrinter:cron')->everyMinute();
Schedule::command('schedulePrinter:cron')->monthly();

// Schedule::command('scheduleMT:cron')->everyMinute();
Schedule::command('scheduleMT:cron')->monthly();

// Schedule::command('scheduleLaptop:cron')->everyMinute();
Schedule::command('scheduleLaptop:cron')->yearly();

// Schedule::command('scheduleComputer:cron')->everyMinute();
Schedule::command('scheduleComputer:cron')->quarterly();


// ===== 🆕 Add VHMS History Schedule =====
Schedule::command('vhms:insert-history')
    ->monthlyOn(1, '00:00'); // Run at midnight every 1st day of the month

// Schedule::command('vhms:insert-history')->everyMinute();