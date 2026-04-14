<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@yeswecange.com'],
            [
                'name'     => 'Admin YesWeCange',
                'password' => Hash::make('iwIXL2iMVA@rDY(Xbp'),
            ]
        );
    }
}
