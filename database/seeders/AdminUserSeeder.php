<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role; 

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = 'admin@example.com';
    
        $admin = User::where('email', $adminEmail)->first();
    
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => $adminEmail,
                'password' => bcrypt('password123'),
            ]);
        }
    
        $admin->assignRole('admin');
    }
    
}
