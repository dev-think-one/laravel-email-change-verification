<?php

namespace EmailChangeVerification\Tests\Fixtures\Factories;

use EmailChangeVerification\Tests\Fixtures\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email'      => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'phone'      => fake()->phoneNumber(),
        ];
    }
}
