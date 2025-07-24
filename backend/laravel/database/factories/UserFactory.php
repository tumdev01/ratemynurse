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
            'firstname' => $this->faker->name(),
            'lastname'  => $this->faker->lastname(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('1'),
            'user_type' => 'ADMIN',
            'status' => 1,
            'religion' => 'BUDDHIST',
            'phone' => '0' . $this->faker->randomNumber(9, true),
            'remember_token' => Str::random(10),
        ];
    }
}
