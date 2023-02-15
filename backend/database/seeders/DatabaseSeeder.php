<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(50)->create();
        Product::factory(500)->create();

        Cart::factory(200)
            ->has(User::factory())
            ->has(Product::factory())
            ->create();
    }
}
