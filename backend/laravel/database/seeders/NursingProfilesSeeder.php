<?php

namespace Database\Seeders;

use App\Models\NursingProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class NursingProfilesSeeder extends Seeder
{
    public function run()
    {
        NursingProfile::query()->insert([
            [
                'id' => 1,
                'user_id' => 3,
                'name' => 'อุณณัญวร จันทร์สว่าง (อุณ)',
                'religion' => 'BUDDHIST',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'gender' => 'FEMALE',
                'cost' => 1800.00,
                'date_of_birth' => '1986-03-25',
                'nationality' => 'THAI',
                'province_id' => 50,
                'district_id' => 5001,
                'sub_district_id' => 500101,
                'zipcode' => 50000,
                'certified' => 1
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'name' => 'นันทิชา ธรรมภักดิ์ (ปาย)',
                'religion' => 'BUDDHIST',
                'gender' => 'FEMALE',
                'date_of_birth' => '1986-03-25',
                'nationality' => 'THAI',
                'cost' => 2750.00,
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5002,
                'sub_district_id' => 500203,
                'zipcode' => 500203,
                'certified' => 1
            ],
            [
                'id' => 3,
                'user_id' => 5,
                'name' => 'สุรพล จันทร์สว่างวงศ์ (แทน)',
                'religion' => 'BUDDHIST',
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'cost' => 1850.00,
                'nationality' => 'THAI',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5003,
                'sub_district_id' => 500301,
                'zipcode' => 500301,
                'certified' => 1
            ],
            [
                'id' => 4,
                'user_id' => 6,
                'name' => 'ชุติภรณ์ รักดี (ชุติ)',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 5,
                'user_id' => 24,
                'name' => 'สมศรี การเรียนดี',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 6,
                'user_id' => 25,
                'name' => 'นฤดี เพียรการงาน',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 7,
                'user_id' => 26,
                'name' => 'นาวา จิตรจำเริญรุ่ง (แนน)',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 8,
                'user_id' => 27,
                'name' => 'ชัญญา วัฒนโกศล (อิ๋ม)',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 9,
                'user_id' => 28,
                'name' => 'ชานนท์ ธนากานต์ (เรย์)',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ],
            [
                'id' => 10,
                'user_id' => 29,
                'name' => 'ณิชาภา วิวัฒนาศักดิ์ (ทัมทิม)',
                'religion' => 'BUDDHIST',
                'nationality' => 'THAI',
                'cost' => 1950.00,
                'date_of_birth' => '1986-03-25',
                'gender' => 'FEMALE',
                'about' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales sagittis augue et facilisis. In vitae nibh at lorem porttitor tristique. In vitae ante semper, dictum mi sed, congue diam.',
                'province_id' => 50,
                'district_id' => 5004,
                'sub_district_id' => 500402,
                'zipcode' => 500402,
                'certified' => 1
            ]
        ]);
    }
}
