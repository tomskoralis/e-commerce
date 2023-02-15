<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedCannotSeeProducts(): void
    {
        $response = $this->get('/api/v1/products', [
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

        $products = Product::factory(3)->create();

        $response = $this->get('/api/v1/products', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'products' => [
                [
                    'id' => $products[2]->id,
                    'name' => $products[2]->name,
                    'available' => $products[2]->available,
                    'price' => number_format(
                        round($products[2]->price_euros + $products[2]->price_cents / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                    'vat_rate' => rtrim(rtrim($products[2]->vat_rate, '0'), '.'),
                    'vat' => number_format(
                        round(($products[2]->price_euros + $products[2]->price_cents / 100) * $products[2]->vat_rate / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                ],
                [
                    'id' => $products[1]->id,
                    'name' => $products[1]->name,
                    'available' => $products[1]->available,
                    'price' => number_format(
                        round($products[1]->price_euros + $products[1]->price_cents / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                    'vat_rate' => rtrim(rtrim($products[1]->vat_rate, '0'), '.'),
                    'vat' => number_format(
                        round(($products[1]->price_euros + $products[1]->price_cents / 100) * $products[1]->vat_rate / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                ],
                [
                    'id' => $products[0]->id,
                    'name' => $products[0]->name,
                    'available' => $products[0]->available,
                    'price' => number_format(
                        round($products[0]->price_euros + $products[0]->price_cents / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                    'vat_rate' => rtrim(rtrim($products[0]->vat_rate, '0'), '.'),
                    'vat' => number_format(
                        round(($products[0]->price_euros + $products[0]->price_cents / 100) * $products[0]->vat_rate / 100, 2),
                        2,
                        '.',
                        ''
                    ),
                ],
            ]
        ]);
    }

    public function testUnauthenticatedCannotSeeProduct(): void
    {
        $response = $this->get('/api/v1/products/1', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testShow(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create();

        $response = $this->get('/api/v1/products/' . $product->id, [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertExactJson([
            'product' => [
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
            ]
        ]);
    }

    public function testUnauthenticatedCannotStoreProduct(): void
    {
        $response = $this->post('/api/v1/products', [
            'name' => 'test product',
            'available' => 99,
            'price' => 123.45,
            'vat_rate' => 21
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testStore(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/api/v1/products', [
            'name' => 'test product',
            'available' => 99,
            'price' => 123.45,
            'vat_rate' => 21
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $responseContent = json_decode($response->getContent());

        $response->assertStatus(201);

        $response->assertExactJson([
            'product' => [
                'id' => $responseContent->product->id,
                'name' => 'test product',
                'available' => 99,
                'price' => '123.45',
                'vat_rate' => '21',
                'vat' => '25.92',
            ]
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'test product',
            'available' => 99,
            'price_euros' => 123,
            'price_cents' => 45,
            'vat_rate' => 21
        ]);
    }

    public function testUnauthenticatedCannotUpdateProduct(): void
    {
        $response = $this->put('/api/v1/products/1', [
            'name' => 'test updated',
            'available' => 95,
            'price' => 543.21,
            'vat_rate' => 18
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUpdate(): void
    {
        $product = Product::factory()->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/api/v1/products/' . $product->id, [
            'name' => 'test updated',
            'available' => 95,
            'price' => 543.21,
            'vat_rate' => 18
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(200);

        $response->assertExactJson([
            'product' => [
                'id' => $product->id,
                'name' => 'test updated',
                'available' => 95,
                'price' => '543.21',
                'vat_rate' => '18',
                'vat' => '97.78',
            ]
        ]);
    }

    public function testUnauthenticatedCannotDestroyProduct(): void
    {
        $response = $this->delete('/api/v1/products/1', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testDestroy(): void
    {
        $product = Product::factory()->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete('/api/v1/products/' . $product->id, [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(204);

        $response->assertNoContent();
    }

    public function testUnauthenticatedCannotSeeOutOfStock(): void
    {
        $response = $this->get('/api/v1/unavailable', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testOutOfStock(): void
    {
        Product::factory()->create([
            'available' => 1,
        ]);

        $product = Product::factory()->create([
            'available' => 0,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/v1/unavailable', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
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
                ],
            ]
        ]);
    }
}
