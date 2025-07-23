<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // สร้าง Admin (user_type=ADMIN จาก factory)
        User::factory()->create();

        // สร้าง user api กำหนดค่าเจาะจง
        User::factory()->create([
            'name' => 'api',
            'email' => 'api@mail.com',
            'password' => Hash::make('1'),
            'user_type' => 'API',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        // สร้าง user แบบสุ่ม 10 คน user_type = NURSING, รหัสผ่าน = '1'
        User::factory()->count(10)->create([
            'user_type' => 'NURSING',
            'password' => Hash::make('1'),
        ]);
    }
}
