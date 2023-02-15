<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'product_id' => Product::query()->inRandomOrder()->first()->id,
            'count' => fake()->numberBetween(1,3),
        ];
    }
}
