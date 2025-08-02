<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class UserSeeder extends Seeder
{
    public function run()
    {
        // สร้าง Admin (user_type=ADMIN จาก factory)
        User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'Manager',
            'email' => 'admin@mail.com',
            'password' => Hash::make('securepassword'),
            'user_type' => 'ADMIN',
            'status' => 1,
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        // สร้าง user api กำหนดค่าเจาะจง
        User::factory()->create([
            'firstname' => 'api',
            'lastname' => 'development',
            'email' => 'api@mail.com',
            'password' => Hash::make('1'),
            'user_type' => 'API',
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        // สร้าง user แบบสุ่ม 10 คน user_type = NURSING, รหัสผ่าน = '1'
        User::factory()->count(10)->create([
            'user_type' => 'NURSING',
            'status' => 1,
            'password' => Hash::make('1'),
        ]);

        User::factory()->create([
            'firstname' => 'Diana Garden Resort Pattaya',
            'lastname' => 'Diana Garden Resort Pattaya',
            'email' => 'diana_garden_resort@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
    }
}
