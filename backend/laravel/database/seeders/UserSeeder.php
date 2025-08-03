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
        User::factory()->create([
            'id' => '3',
            'firstname' => 'อุณณัญวร',
            'lastname' => 'จันทร์สว่าง (อุณ)',
            'email' => 'test1@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '4',
            'firstname' => 'นันทิชา',
            'lastname' => 'ธรรมภักดิ์ (ปาย)',
            'email' => 'test2@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '5',
            'firstname' => 'สุรพล',
            'lastname' => 'จันทร์สว่างวงศ์ (แทน)',
            'email' => 'test3@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '6',
            'firstname' => 'ชุติภรณ์',
            'lastname' => 'รักดี (ชุติ)',
            'email' => 'test4@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '13',
            'firstname' => 'Diana Garden Resort Pattaya',
            'lastname' => 'Diana Garden Resort Pattaya',
            'email' => 'test5@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '14',
            'firstname' => 'บ้านลลิสาเนอร์ซิ่งโฮม สาขาเชียงราย',
            'lastname' => 'บ้านลลิสาเนอร์ซิ่งโฮม สาขาเชียงราย',
            'email' => 'test6@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '15',
            'firstname' => 'บ้านลลิสา สาขาพิษณุโลก',
            'lastname' => 'บ้านลลิสา สาขาพิษณุโลก',
            'email' => 'test7@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '16',
            'firstname' => 'บ้านลลิสา สาขากำแพงเพชร',
            'lastname' => 'บ้านลลิสา สาขากำแพงเพชร',
            'email' => 'test8@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
    }
}
