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
            ],
            [
                'id' => 70,
                'name' => 'ราชบุรี',
                'code' => 'RBR',
            ],
            [
                'id' => 66,
                'name' => 'พิจิตร',
                'code' => 'PCT',
            ],
            [
                'id' => 67,
                'name' => 'เพชรบูรณ์',
                'code' => 'PNB',
                
            ],
            [
                'id' => 65,
                'name' => 'พิษณุโลก',
                'code' => 'PLK',
                
            ],
            [
                'id' => 64,
                'name' => 'สุโขทัย',
                'code' => 'STI',
                
            ],
            [
                'id' => 63,
                'name' => 'ตาก',
                'code' => 'TAK',
                
            ],
            [
                'id' => 62,
                'name' => 'กำแพงเพชร',
                'code' => 'KPT',
                
            ],
            [
                'id' => 61,
                'name' => 'อุทัยธานี',
                'code' => 'UTI',
                
            ],
            [
                'id' => 60,
                'name' => 'นครสวรรค์',
                'code' => 'NSN',
                
            ],
            [
                'id' => 58,
                'name' => 'แม่ฮ่องสอน',
                'code' => 'MSN',
                
            ],
            [
                'id' => 57,
                'name' => 'เชียงราย',
                'code' => 'CRI',
                
            ],
            [
                'id' => 56,
                'name' => 'พะเยา',
                'code' => 'PYO',
                
            ],
            [
                'id' => 55,
                'name' => 'น่าน',
                'code' => 'NAN',
                
            ],
            [
                'id' => 54,
                'name' => 'แพร่',
                'code' => 'PRE',
                
            ],
            [
                'id' => 53,
                'name' => 'อุตรดิตถ์',
                'code' => 'UTD',
                
            ],
            [
                'id' => 52,
                'name' => 'ลำปาง',
                'code' => 'LPG',
                
            ],
            [
                'id' => 51,
                'name' => 'ลำพูน',
                'code' => 'LPN',
                
            ],
            [
                'id' => 49,
                'name' => 'มุกดาหาร',
                'code' => 'MDH',
                
            ],
            [
                'id' => 50,
                'name' => 'เชียงใหม่',
                'code' => 'CMI',
                
            ],
            [
                'id' => 48,
                'name' => 'นครพนม',
                'code' => 'NPM',
                
            ],
            [
                'id' => 47,
                'name' => 'สกลนคร',
                'code' => 'SNK',
                
            ],
            [
                'id' => 46,
                'name' => 'กาฬสินธุ์',
                'code' => 'KSN',
                
            ],
            [
                'id' => 44,
                'name' => 'มหาสารคาม',
                'code' => 'MKM',
                
            ],
            [
                'id' => 45,
                'name' => 'ร้อยเอ็ด',
                'code' => 'RET',
                
            ],
            [
                'id' => 43,
                'name' => 'หนองคาย',
                'code' => 'NKI',
                
            ],
            [
                'id' => 42,
                'name' => 'เลย',
                'code' => 'LEI',
                
            ],
            [
                'id' => 41,
                'name' => 'อุดรธานี',
                'code' => 'UDN',
                
            ],
            [
                'id' => 40,
                'name' => 'ขอนแก่น',
                'code' => 'KKN',
                
            ],
            [
                'id' => 39,
                'name' => 'หนองบัวลำภู',
                'code' => 'NBP',
                
            ],
            [
                'id' => 37,
                'name' => 'อำนาจเจริญ',
                'code' => 'ACR',
                
            ],
            [
                'id' => 36,
                'name' => 'ชัยภูมิ',
                'code' => 'CPM',
                
            ],
            [
                'id' => 35,
                'name' => 'ยโสธร',
                'code' => 'YST',
                
            ],
            [
                'id' => 34,
                'name' => 'อุบลราชธานี',
                'code' => 'UBN',
                
            ],
            [
                'id' => 33,
                'name' => 'ศรีสะเกษ',
                'code' => 'SSK',
                
            ],
            [
                'id' => 32,
                'name' => 'สุรินทร์',
                'code' => 'SRN',
                
            ],
            [
                'id' => 31,
                'name' => 'บุรีรัมย์',
                'code' => 'BRM',
                
            ],
            [
                'id' => 30,
                'name' => 'นครราชสีมา',
                'code' => 'NMA',
                
            ],
            [
                'id' => 27,
                'name' => 'สระแก้ว',
                'code' => 'SKW',
                
            ],
            [
                'id' => 26,
                'name' => 'นครนายก',
                'code' => 'NYK',
                
            ],
            [
                'id' => 25,
                'name' => 'ปราจีนบุรี',
                'code' => 'PRI',
                
            ],
            [
                'id' => 24,
                'name' => 'ฉะเชิงเทรา',
                'code' => 'CCO',
                
            ],
            [
                'id' => 23,
                'name' => 'ตราด',
                'code' => 'TRT',
                
            ],
            [
                'id' => 22,
                'name' => 'จันทบุรี',
                'code' => 'CTI',
                
            ],
            [
                'id' => 21,
                'name' => 'ระยอง',
                'code' => 'RYG',
                
            ],
            [
                'id' => 20,
                'name' => 'ชลบุรี',
                'code' => 'CBI',
                
            ],
            [
                'id' => 19,
                'name' => 'สระบุรี',
                'code' => 'SRI',
                
            ],
            [
                'id' => 18,
                'name' => 'ชัยนาท',
                'code' => 'CNT',
                
            ],
            [
                'id' => 17,
                'name' => 'สิงห์บุรี',
                'code' => 'SBR',
                
            ],
            [
                'id' => 16,
                'name' => 'ลพบุรี',
                'code' => 'LRI',
                
            ],
            [
                'id' => 15,
                'name' => 'อ่างทอง',
                'code' => 'ATG',
                
            ],
            [
                'id' => 14,
                'name' => 'พระนครศรีอยุธยา',
                'code' => 'AYA',
                
            ],
            [
                'id' => 13,
                'name' => 'ปทุมธานี',
                'code' => 'PTE',
                
            ],
            [
                'id' => 12,
                'name' => 'นนทบุรี',
                'code' => 'NBI',
                
            ],
            [
                'id' => 11,
                'name' => 'สมุทรปราการ',
                'code' => 'SPK',
                
            ],
            [
                'id' => 10,
                'name' => 'กรุงเทพมหานคร',
                'code' => 'BKK',
                
            ],
            [
                'id' => 72,
                'name' => 'สุพรรณบุรี',
                'code' => 'SPB',
                
            ],
            [
                'id' => 73,
                'name' => 'นครปฐม',
                'code' => 'NPT',
                
            ],
            [
                'id' => 74,
                'name' => 'สมุทรสาคร',
                'code' => 'SKN',
                
            ],
            [
                'id' => 75,
                'name' => 'สมุทรสงคราม',
                'code' => 'SKM',
                
            ],
            [
                'id' => 76,
                'name' => 'เพชรบุรี',
                'code' => 'PBI',
                
            ],
            [
                'id' => 77,
                'name' => 'ประจวบคีรีขันธ์',
                'code' => 'PKN',
                
            ],
            [
                'id' => 80,
                'name' => 'นครศรีธรรมราช',
                'code' => 'NRT',
                
            ],
            [
                'id' => 81,
                'name' => 'กระบี่',
                'code' => 'KBI',
                
            ],
            [
                'id' => 82,
                'name' => 'พังงา',
                'code' => 'PNA',
                
            ],
            [
                'id' => 83,
                'name' => 'ภูเก็ต',
                'code' => 'PKT',
                
            ],
            [
                'id' => 84,
                'name' => 'สุราษฎร์ธานี',
                'code' => 'SNI',
                
            ],
            [
                'id' => 85,
                'name' => 'ระนอง',
                'code' => 'RNG',
                
            ],
            [
                'id' => 86,
                'name' => 'ชุมพร',
                'code' => 'CPN',
                
            ],
            [
                'id' => 90,
                'name' => 'สงขลา',
                'code' => 'SKA',
                
            ],
            [
                'id' => 91,
                'name' => 'สตูล',
                'code' => 'STN',
                
            ],
            [
                'id' => 92,
                'name' => 'ตรัง',
                'code' => 'TRG',
                
            ],
            [
                'id' => 93,
                'name' => 'พัทลุง',
                'code' => 'PLG',
                
            ],
            [
                'id' => 94,
                'name' => 'ปัตตานี',
                'code' => 'PTN',
                
            ],
            [
                'id' => 95,
                'name' => 'ยะลา',
                'code' => 'YLA',
                
            ],
            [
                'id' => 96,
                'name' => 'นราธิวาส',
                'code' => 'NWT',
            ],
                
            [
                'id' => 38,
                'name' => 'บึงกาฬ',
                'code' => 'BKN',
            ]
        ]);
    }
}
