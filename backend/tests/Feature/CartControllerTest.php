<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedCannotSeeCart(): void
    {
        $response = $this->get('/api/v1/cart', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testIndex(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $cart = (new Cart())->fill([
            'count' => 1,
        ]);
        $cart->user()->associate($user);
        $cart->product()->associate($product);
        $cart->save();

        $response = $this->get('/api/v1/cart', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertExactJson([
            'cart' => [
                'products' => [
                    [
                        'id' => $product->id,
                        'name' => $product->name,
                        'available' => $product->available,
                        'price' => number_format(
                            round($product->price_euros + $product->price_cents / 100, 2),
                            2,
                            '.',
                            ''
                        ),
                        'vat_rate' => rtrim(rtrim($product->vat_rate, '0'), '.'),
                        'vat' => number_format(
                            round(($product->price_euros + $product->price_cents / 100) * $product->vat_rate / 100, 2),
                            2,
                            '.',
                            ''
                        ),
                        'count' => 1
                    ]
                ],
                'subtotal' => number_format(
                    round($product->price_euros + $product->price_cents / 100, 2),
                    2,
                    '.',
                    ''
                ),
                'vat' => number_format(
                    round(($product->price_euros + $product->price_cents / 100) * $product->vat_rate / 100, 2),
                    2,
                    '.',
                    ''
                ),
                'total' => number_format(
                    round(($product->price_euros + $product->price_cents / 100) * (1 + $product->vat_rate / 100), 2),
                    2,
                    '.',
                    ''
                ),
            ]
        ]);
    }

    public function testUnauthenticatedCannotAddProduct(): void
    {
        $response = $this->post('/api/v1/cart/', [
            'id' => 1,
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testAddProduct(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $response = $this->post('/api/v1/cart/', [
            'id' => $product->id,
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(201);

        $response->assertExactJson([
            'cart' => [
                'products' => [
                    [
                        'id' => $product->id,
                        'name' => $product->name,
                        'available' => $product->available,
                        'price' => number_format(
                            round($product->price_euros + $product->price_cents / 100, 2),
                            2,
                            '.',
                            ''
                        ),
                        'vat_rate' => rtrim(rtrim($product->vat_rate, '0'), '.'),
                        'vat' => number_format(
                            round(($product->price_euros + $product->price_cents / 100) * $product->vat_rate / 100, 2),
                            2,
                            '.',
                            ''
                        ),
                        'count' => 1
                    ]
                ],
                'subtotal' => number_format(
                    round($product->price_euros + $product->price_cents / 100, 2),
                    2,
                    '.',
                    ''
                ),
                'vat' => number_format(
                    round(($product->price_euros + $product->price_cents / 100) * $product->vat_rate / 100, 2),
                    2,
                    '.',
                    ''
                ),
                'total' => number_format(
                    round(($product->price_euros + $product->price_cents / 100) * (1 + $product->vat_rate / 100), 2),
                    2,
                    '.',
                    ''
                ),
            ]
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'count' => 1,
        ]);
    }

    public function testUnauthenticatedCannotRemoveProduct(): void
    {
        $response = $this->delete('/api/v1/cart', [
            'id' => 1,
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testRemoveProduct(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $cart = (new Cart())->fill([
            'count' => 1,
        ]);
        $cart->user()->associate($user);
        $cart->product()->associate($product);
        $cart->save();

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'count' => 1,
        ]);

        $response = $this->delete('/api/v1/cart', [
            'id' => $product->id,
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(200);

        $response->assertExactJson([
            'cart' => [
                'products' => [],
                'subtotal' => '0.00',
                'vat' => '0.00',
                'total' => '0.00',
            ]
        ]);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'count' => 1,
            'status' => null,
        ]);
    }

    public function testUnauthenticatedCannotCheckout(): void
    {
        $response = $this->post('/api/v1/checkout', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testCheckout(): void
    {
        $user = User::factory()->create([
            'balance_euros' => 121,
            'balance_cents' => 3,
        ]);
        $this->actingAs($user);

        $product = Product::factory()->create([
            'price_euros' => 99,
            'price_cents' => 98,
            'available' => 1,
            'vat_rate' => 21,
        ]);

        $cart = (new Cart())->fill([
            'count' => 1,
        ]);
        $cart->user()->associate($user);
        $cart->product()->associate($product);
        $cart->save();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
            'available' => 1,
        ]);

        $response = $this->post('/api/v1/checkout', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertExactJson([
            'message' => 'Successfully bought the items in the cart.'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'balance_euros' => 0,
            'balance_cents' => 5,
        ]);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'count' => 1,
            'status' => 'bought'
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name,
            'available' => 0,
        ]);
    }
}
