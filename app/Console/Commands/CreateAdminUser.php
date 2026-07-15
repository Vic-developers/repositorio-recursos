<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create or reset the admin user';

    public function handle()
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'principal'],
            ['name' => 'Institución Principal', 'is_active' => true, 'settings' => ['locale' => 'es', 'timezone' => 'America/Mexico_City']]
        );

        $user = User::updateOrCreate(
            ['email' => 'admin@repositorio.edu'],
            [
                'tenant_id' => $tenant->id,
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'password' => Hash::make('Admin123!'),
                'role' => 'Admin',
                'is_active' => true,
            ]
        );

        $this->info("Admin user created: admin@repositorio.edu / Admin123!");
    }
}
