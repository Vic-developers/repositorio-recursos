<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'principal'],
            ['name' => 'Institución Principal', 'is_active' => true, 'settings' => ['locale' => 'es', 'timezone' => 'America/Mexico_City']]
        );

        if (User::where('email', 'admin@repositorio.edu')->exists()) {
            return;
        }

        $defaults = [
            'general' => ['system_name' => 'Repositorio Educativo', 'system_description' => 'Plataforma de recursos educativos', 'default_language' => 'es', 'timezone' => 'America/Mexico_City', 'logo_url' => ''],
            'modules' => ['resources' => '1', 'folders' => '1', 'categories' => '1', 'tags' => '1', 'scorm' => '1', 'h5p' => '1', 'favorites' => '1', 'sharing' => '1', 'trash' => '1'],
            'scorm' => ['default_version' => '1.2', 'max_file_size_mb' => '500'],
            'embed' => ['allowed_domains' => '', 'enable_frame_ancestors' => '1'],
        ];
        foreach ($defaults as $module => $settings) {
            foreach ($settings as $key => $value) {
                Setting::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'module' => $module, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        User::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Admin',
            'last_name' => 'Sistema',
            'email' => 'admin@repositorio.edu',
            'password' => Hash::make('Admin123!'),
            'role' => 'Admin',
            'is_active' => true,
            'language' => 'es',
            'theme' => 'light',
        ]);
    }
}
