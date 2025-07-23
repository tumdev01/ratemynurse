<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('1'), // รหัสผ่าน '1' hash เดียวกัน
            'user_type' => 'ADMIN', // ค่าดีฟอลต์ ถ้าอยากเปลี่ยนให้ seed ระบุค่าเอง
            'remember_token' => Str::random(10),
        ];
    }
}
