<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MatrixUserSeeder extends Seeder
{
    public function run()
    {
        // Create matrix user
        User::create([
            'name' => 'Matrix User',
            'email' => 'matrix@matrix.com',
            'password' => Hash::make('password'),
            'role' => 'matrix_user',
            'is_active' => true,
        ]);
    }
}
