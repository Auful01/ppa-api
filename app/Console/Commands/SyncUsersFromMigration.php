<?php

namespace App\Console\Commands;

use App\Models\UserAll;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncUsersFromMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user_alls';

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
        $this->info('Starting sync process...');

        $users = DB::table('migration_user')
            ->whereNull('nonactive_date')
            ->get();

        foreach ($users as $user) {
            // $existingUser = DB::table('user_alls')->where('nrp', $user->nrp)->first();
            $existingUser = Cache::remember("user_alls_{$user->nrp}", 60, function () use ($user) {
                return UserAll::where('nrp', $user->nrp)->first();
            });
            if ($existingUser) {
                // Perbarui hanya jika ada perubahan data
                $existingUser->username = $user->nama;
                $existingUser->department = $user->dept;
                $existingUser->position = $user->jabatan;
                $existingUser->email = $user->email;
                $existingUser->site = $user->site;

                // Hanya update jika ada perubahan
                if ($existingUser->isDirty()) {
                    $existingUser->updated_at = now();
                    $existingUser->save();
                    $this->info("Updated user: {$user->nrp} - {$user->nama}");
                }
            } else {
                // Insert jika user belum ada
                UserAll::create([
                    'nrp' => $user->nrp,
                    'username' => $user->nama,
                    'department' => $user->dept,
                    'position' => $user->jabatan,
                    'email' => $user->email,
                    'site' => $user->site,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Tambahkan ke cache agar tidak perlu query lagi
                Cache::put("user_alls_{$user->nrp}", $user, 60);

                $this->info("Inserted new user: {$user->nrp} - {$user->nama}");
            }
        }

        $this->info('Sync process completed.');
    }
}
