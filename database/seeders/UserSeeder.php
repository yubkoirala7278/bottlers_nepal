<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create inbound staff user
        User::create([
            'name' => 'Inbound Staff',
            'email' => 'inbound@inbound.com',
            'password' => Hash::make('password'),
            'role' => 'inbound_staff',
            'is_active' => true,
        ]);

        // Create outbound staff user
        User::create([
            'name' => 'Outbound Staff',
            'email' => 'outbound@outbound.com',
            'password' => Hash::make('password'),
            'role' => 'outbound_staff',
            'is_active' => true,
        ]);
    }
}
