<?php

namespace Database\Seeders;

use App\Models\Rate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RateSeeder extends Seeder
{
    public function run()
    {

        Rate::query()->insert([
            [
                'id' => 1,
                'user_id' => 3,
                'scores' => 3,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'scores' => 4,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'scores' => 5,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 4,
                'user_id' => 3,
                'scores' => 3,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],

            [
                'id' => 5,
                'user_id' => 4,
                'scores' => 3,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 6,
                'user_id' => 4,
                'scores' => 4,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 7,
                'user_id' => 4,
                'scores' => 5,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
            [
                'id' => 8,
                'user_id' => 5,
                'scores' => 3,
                'user_type' => 'NURSING',
                'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eleifend sem at ex egestas tempus.',
            ],
        ]);
        
        
    }
}
