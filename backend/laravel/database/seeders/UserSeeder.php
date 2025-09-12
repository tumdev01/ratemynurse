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

        User::factory()->create([
            'firstname' => 'Editor1',
            'lastname' => 'Manager',
            'email' => 'editor1@mail.com',
            'password' => Hash::make('securepassword'),
            'user_type' => 'ADMIN',
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'firstname' => 'Editor2',
            'lastname' => 'Manager',
            'email' => 'editor2@mail.com',
            'password' => Hash::make('securepassword'),
            'user_type' => 'ADMIN',
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

        User::factory()->create([
            'id' => '17',
            'firstname' => 'บ้านลลิสา สาขาบางนา',
            'lastname' => 'บ้านลลิสา สาขาบางนา',
            'email' => 'test9@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '18',
            'firstname' => 'บ้านลลิสา สาขาพัทยา',
            'lastname' => 'บ้านลลิสา สาขาพัทยา',
            'email' => 'test10@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '19',
            'firstname' => 'บ้านลลิสา ศาลายานครปฐม',
            'lastname' => 'บ้านลลิสา ศาลายานครปฐม',
            'email' => 'test11@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        
        User::factory()->create([
            'id' => '20',
            'firstname' => 'บ้านลลิสา สาขางามวงศ์วาน',
            'lastname' => 'บ้านลลิสา สาขางามวงศ์วาน',
            'email' => 'test12@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '21',
            'firstname' => 'บ้านลลิสา เนอร์สซิ่งโฮม กรุงเทพ (รังสิต)',
            'lastname' => 'บ้านลลิสา เนอร์สซิ่งโฮม กรุงเทพ (รังสิต)',
            'email' => 'test13@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        
        User::factory()->create([
            'id' => '22',
            'firstname' => 'บ้านลลิสา เนอร์สซิ่งโฮม ฉะเชิงเทรา',
            'lastname' => 'บ้านลลิสา เนอร์สซิ่งโฮม ฉะเชิงเทรา',
            'email' => 'test14@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '23',
            'firstname' => 'อมเรศ',
            'lastname' => 'แก้วประทุม (ตั้ม)',
            'email' => 'tum.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '24',
            'firstname' => 'สมศรี',
            'lastname' => 'การเรียนดี',
            'email' => 'somsri.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '25',
            'firstname' => 'นฤดี',
            'lastname' => 'เพียรการงาน',
            'email' => 'narudee.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '26',
            'firstname' => 'นาวา',
            'lastname' => 'จิตรจำเริญรุ่ง (แนน)',
            'email' => 'nava.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        User::factory()->create([
            'id' => '27',
            'firstname' => 'ชัญญา',
            'lastname' => 'วัฒนโกศล (อิ๋ม)',
            'email' => 'chanya.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        User::factory()->create([
            'id' => '28',
            'firstname' => 'ชานนท์',
            'lastname' => 'ธนากานต์ (เรย์)',
            'email' => 'chanon.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        User::factory()->create([
            'id' => '29',
            'firstname' => 'ณิชาภา',
            'lastname' => 'วิวัฒนาศักดิ์ (ทัมทิม)',
            'email' => 'nichapha.test@mail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '30',
            'firstname' => 'เฮลปิ้ง แฮนด์ เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'lastname' => 'เฮลปิ้ง แฮนด์ เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'email' => 'admin@thaihelpinghands.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '31',
            'firstname' => 'โคซี่ แคร์ วิลเลจ บ้านพักหลังวัยเกษียณ การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'lastname' => 'โคซี่ แคร์ วิลเลจ บ้านพักหลังวัยเกษียณ การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'email' => 'cozycarevillage@gmail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '32',
            'firstname' => 'เอส.ดี.เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'lastname' => 'เอส.ดี.เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'email' => 'krisanu_15@hotmail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);

        User::factory()->create([
            'id' => '33',
            'firstname' => 'ดอยสะเก็ด เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'lastname' => 'ดอยสะเก็ด เนอร์สซิ่งโฮม การดูแลผู้สูงอายุหรือผู้มีภาวะพึ่งพิง',
            'email' => 'doisaket.nursinghome@gmail.com',
            'password' => Hash::make('1'),
            'user_type' => UserType::NURSING_HOME->value,
            'status' => '1',
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        
        
    }
}
