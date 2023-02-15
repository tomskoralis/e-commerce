<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 0.01, 999);
        $euros = floor($price);
        return [
            'name' => fake()->unique()->words(mt_rand(2, 4), true),
            'available' => fake()->numberBetween(0, 20),
            'price_euros' => $euros,
            'price_cents' => ($price - $euros) * 100,
            'vat_rate' => 21,
        ];
    }
}
