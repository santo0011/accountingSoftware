<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            RolePermissionSeeder::class,
            CatalogSeeder::class,
            ServiceTaglineSeeder::class,
            CmsSeeder::class,
            UserSeeder::class,
        ]);

        // Sample customers, applications, payments, leads, compliance and tickets — local/demo only.
        if (app()->environment('local') || config('app.seed_demo_data')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
