<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_product()
    {
        $response = $this->postJson('/api/odata/products', [
            'name' => 'Laptop',
            'description' => 'A high-end gaming laptop',
            'price' => 1500.00,
            'category_id' => 1,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'description', 'price', 'category_id']);
    }

    /** @test */
    public function it_fetches_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/odata/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }
}
