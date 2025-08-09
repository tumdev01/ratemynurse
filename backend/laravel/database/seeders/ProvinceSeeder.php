<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Province::query()->insert([
            [
                'id' => 71,
                'name' => 'กาญจนบุรี',
                'code' => 'KRI',
                'zone' => 'WEST'
            ],
            [
                'id' => 70,
                'name' => 'ราชบุรี',
                'code' => 'RBR',
                'zone' => 'WEST'
            ],
            [
                'id' => 66,
                'name' => 'พิจิตร',
                'code' => 'PCT',
                'zone' => 'MID'
            ],
            [
                'id' => 67,
                'name' => 'เพชรบูรณ์',
                'code' => 'PNB',
                'zone' => 'MID'
            ],
            [
                'id' => 65,
                'name' => 'พิษณุโลก',
                'code' => 'PLK',
                'zone' => 'NORTH'
            ],
            [
                'id' => 64,
                'name' => 'สุโขทัย',
                'code' => 'STI',
                'zone' => 'NORTH'
            ],
            [
                'id' => 63,
                'name' => 'ตาก',
                'code' => 'TAK',
                'zone' => 'NORTH'
            ],
            [
                'id' => 62,
                'name' => 'กำแพงเพชร',
                'code' => 'KPT',
                'zone' => 'MID'
            ],
            [
                'id' => 61,
                'name' => 'อุทัยธานี',
                'code' => 'UTI',
                'zone' => 'MID'
            ],
            [
                'id' => 60,
                'name' => 'นครสวรรค์',
                'code' => 'NSN',
                'zone' => 'MID'
            ],
            [
                'id' => 58,
                'name' => 'แม่ฮ่องสอน',
                'code' => 'MSN',
                'zone' => 'NORTH'
            ],
            [
                'id' => 57,
                'name' => 'เชียงราย',
                'code' => 'CRI',
                'zone' => 'NORTH'
            ],
            [
                'id' => 56,
                'name' => 'พะเยา',
                'code' => 'PYO',
                'zone' => 'NORTH'
            ],
            [
                'id' => 55,
                'name' => 'น่าน',
                'code' => 'NAN',
                'zone' => 'NORTH'
            ],
            [
                'id' => 54,
                'name' => 'แพร่',
                'code' => 'PRE',
                'zone' => 'NORTH'
            ],
            [
                'id' => 53,
                'name' => 'อุตรดิตถ์',
                'code' => 'UTD',
                'zone' => 'NORTH'
            ],
            [
                'id' => 52,
                'name' => 'ลำปาง',
                'code' => 'LPG',
                'zone' => 'NORTH'
            ],
            [
                'id' => 51,
                'name' => 'ลำพูน',
                'code' => 'LPN',
                'zone' => 'NORTH'
            ],
            [
                'id' => 49,
                'name' => 'มุกดาหาร',
                'code' => 'MDH',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 50,
                'name' => 'เชียงใหม่',
                'code' => 'CMI',
                'zone' => 'NORTH'
            ],
            [
                'id' => 48,
                'name' => 'นครพนม',
                'code' => 'NPM',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 47,
                'name' => 'สกลนคร',
                'code' => 'SNK',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 46,
                'name' => 'กาฬสินธุ์',
                'code' => 'KSN',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 44,
                'name' => 'มหาสารคาม',
                'code' => 'MKM',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 45,
                'name' => 'ร้อยเอ็ด',
                'code' => 'RET',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 43,
                'name' => 'หนองคาย',
                'code' => 'NKI',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 42,
                'name' => 'เลย',
                'code' => 'LEI',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 41,
                'name' => 'อุดรธานี',
                'code' => 'UDN',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 40,
                'name' => 'ขอนแก่น',
                'code' => 'KKN',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 39,
                'name' => 'หนองบัวลำภู',
                'code' => 'NBP',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 37,
                'name' => 'อำนาจเจริญ',
                'code' => 'ACR',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 36,
                'name' => 'ชัยภูมิ',
                'code' => 'CPM',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 35,
                'name' => 'ยโสธร',
                'code' => 'YST',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 34,
                'name' => 'อุบลราชธานี',
                'code' => 'UBN',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 33,
                'name' => 'ศรีสะเกษ',
                'code' => 'SSK',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 32,
                'name' => 'สุรินทร์',
                'code' => 'SRN',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 31,
                'name' => 'บุรีรัมย์',
                'code' => 'BRM',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 30,
                'name' => 'นครราชสีมา',
                'code' => 'NMA',
                'zone' => 'NORTHEAST'
            ],
            [
                'id' => 27,
                'name' => 'สระแก้ว',
                'code' => 'SKW',
                'zone' => 'EAST'
            ],
            [
                'id' => 26,
                'name' => 'นครนายก',
                'code' => 'NYK',
                'zone' => 'MID'
            ],
            [
                'id' => 25,
                'name' => 'ปราจีนบุรี',
                'code' => 'PRI',
                'zone' => 'EAST'
            ],
            [
                'id' => 24,
                'name' => 'ฉะเชิงเทรา',
                'code' => 'CCO',
                'zone' => 'EAST'
            ],
            [
                'id' => 23,
                'name' => 'ตราด',
                'code' => 'TRT',
                'zone' => 'EAST'
            ],
            [
                'id' => 22,
                'name' => 'จันทบุรี',
                'code' => 'CTI',
                'zone' => 'EAST'
            ],
            [
                'id' => 21,
                'name' => 'ระยอง',
                'code' => 'RYG',
                'zone' => 'EAST'
            ],
            [
                'id' => 20,
                'name' => 'ชลบุรี',
                'code' => 'CBI',
                'zone' => 'EAST'
            ],
            [
                'id' => 19,
                'name' => 'สระบุรี',
                'code' => 'SRI',
                'zone' => 'MID'
            ],
            [
                'id' => 18,
                'name' => 'ชัยนาท',
                'code' => 'CNT',
                'zone' => 'MID'
            ],
            [
                'id' => 17,
                'name' => 'สิงห์บุรี',
                'code' => 'SBR',
                'zone' => 'MID'
            ],
            [
                'id' => 16,
                'name' => 'ลพบุรี',
                'code' => 'LRI',
                'zone' => 'MID'
            ],
            [
                'id' => 15,
                'name' => 'อ่างทอง',
                'code' => 'ATG',
                'zone' => 'MID'
            ],
            [
                'id' => 14,
                'name' => 'พระนครศรีอยุธยา',
                'code' => 'AYA',
                'zone' => 'MID'
            ],
            [
                'id' => 13,
                'name' => 'ปทุมธานี',
                'code' => 'PTE',
                'zone' => 'MID'
            ],
            [
                'id' => 12,
                'name' => 'นนทบุรี',
                'code' => 'NBI',
                'zone' => 'MID'
            ],
            [
                'id' => 11,
                'name' => 'สมุทรปราการ',
                'code' => 'SPK',
                'zone' => 'MID'
            ],
            [
                'id' => 10,
                'name' => 'กรุงเทพมหานคร',
                'code' => 'BKK',
                'zone' => 'MID'
            ],
            [
                'id' => 72,
                'name' => 'สุพรรณบุรี',
                'code' => 'SPB',
                'zone' => 'WEST'
            ],
            [
                'id' => 73,
                'name' => 'นครปฐม',
                'code' => 'NPT',
                'zone' => 'MID'
            ],
            [
                'id' => 74,
                'name' => 'สมุทรสาคร',
                'code' => 'SKN',
                'zone' => 'WEST'
            ],
            [
                'id' => 75,
                'name' => 'สมุทรสงคราม',
                'code' => 'SKM',
                'zone' => 'WEST'
            ],
            [
                'id' => 76,
                'name' => 'เพชรบุรี',
                'code' => 'PBI',
                'zone' => 'WEST'
            ],
            [
                'id' => 77,
                'name' => 'ประจวบคีรีขันธ์',
                'code' => 'PKN',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 80,
                'name' => 'นครศรีธรรมราช',
                'code' => 'NRT',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 81,
                'name' => 'กระบี่',
                'code' => 'KBI',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 82,
                'name' => 'พังงา',
                'code' => 'PNA',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 83,
                'name' => 'ภูเก็ต',
                'code' => 'PKT',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 84,
                'name' => 'สุราษฎร์ธานี',
                'code' => 'SNI',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 85,
                'name' => 'ระนอง',
                'code' => 'RNG',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 86,
                'name' => 'ชุมพร',
                'code' => 'CPN',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 90,
                'name' => 'สงขลา',
                'code' => 'SKA',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 91,
                'name' => 'สตูล',
                'code' => 'STN',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 92,
                'name' => 'ตรัง',
                'code' => 'TRG',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 93,
                'name' => 'พัทลุง',
                'code' => 'PLG',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 94,
                'name' => 'ปัตตานี',
                'code' => 'PTN',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 95,
                'name' => 'ยะลา',
                'code' => 'YLA',
                'zone' => 'SOUTH'
            ],
            [
                'id' => 96,
                'name' => 'นราธิวาส',
                'code' => 'NWT',
                'zone' => 'SOUTH'
            ],
                
            [
                'id' => 38,
                'name' => 'บึงกาฬ',
                'code' => 'BKN',
                'zone' => 'NORTHEAST'
            ]
        ]);
    }
}
