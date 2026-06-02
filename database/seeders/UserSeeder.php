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
            'email' => 'inbound@bottlersnepal.com',
            'password' => Hash::make('Inbound@123'),
            'role' => 'inbound_staff',
            'is_active' => true,
        ]);

        // Create outbound staff user
        User::create([
            'name' => 'Outbound Staff',
            'email' => 'outbound@bottlersnepal.com',
            'password' => Hash::make('Outbound@123'),
            'role' => 'outbound_staff',
            'is_active' => true,
        ]);
    }
}
