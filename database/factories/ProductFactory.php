<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $sellingPrice = fake()->numberBetween(5000, 35000);

        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'name' => fake()->words(2, true),
            'unit' => 'porsi',
            'selling_price' => $sellingPrice,
            'cost_price' => (int) round($sellingPrice * 0.6),
            'stock_qty' => fake()->numberBetween(5, 40),
            'minimum_stock' => 5,
            'track_stock' => true,
            'image_path' => null,
            'is_active' => true,
        ];
    }

    public function untracked(): static
    {
        return $this->state(fn (array $attributes) => [
            'track_stock' => false,
            'stock_qty' => 0,
            'minimum_stock' => 0,
        ]);
    }
}
