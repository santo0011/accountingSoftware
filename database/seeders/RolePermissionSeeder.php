<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];
        foreach (config('rbac.modules') as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $all[] = Permission::findOrCreate("$module.$action", 'web')->name;
            }
        }

        foreach (config('rbac.roles') as $name => $definition) {
            $role = Role::findOrCreate($name, 'web');
            $granted = collect($definition['permissions']);

            $permissions = $granted->contains('*')
                ? collect($all)->reject(fn ($p) => $granted->contains('!'.$p))
                : $granted;

            $role->syncPermissions($permissions->values()->all());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
