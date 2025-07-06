<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{

    public function run()
    {
        // Pulisci tabelle ruoli e permessi (evita duplicati)
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Role::truncate();
        Permission::truncate();

        // Crea permessi
        $permissions = [
            'view backend',
            'edit backend',
            'delete backend',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crea ruoli
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assegna permessi ai ruoli
        $adminRole->givePermissionTo($permissions);
        // $userRole->givePermissionTo(['view backend']); // eventuale assegnazione a user
    }
}
