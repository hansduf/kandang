<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRolesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Spatie Permission roles to existing users based on their role column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'pemilik', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'pekerja', 'guard_name' => 'web']);

        // Assign roles to existing users
        $users = User::all();
        $assigned = 0;

        foreach ($users as $user) {
            $role = $user->role ?? 'pekerja';
            
            // Remove all existing roles first
            $user->syncRoles([]);
            
            // Assign the correct role
            if ($role === 'pemilik') {
                $user->assignRole('pemilik');
            } elseif ($role === 'pekerja') {
                $user->assignRole('pekerja');
            }
            
            $assigned++;
        }

        $this->info("✓ Assigned roles to {$assigned} users");
    }
}
