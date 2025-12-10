<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'T-Shirt',
                'short_description' => 'Cotton round neck t-shirt',
                'weight' => 50.30,
                'price' => 750,
                'customer_id' => 1,
                'status' => 'packed',
            ],
            [
                'name' => 'Running Shoes',
                'short_description' => 'Sports running shoes',
                'weight' => 90,
                'price' => 450.00,
                'customer_id' => 1,
                'status' => 'packed',
            ],
            [
                'name' => 'Leather Jacket',
                'short_description' => 'Black leather winter jacket',
                'weight' => 80,
                'price' => 600.00,
                'customer_id' => 2,
                'status' => 'packed',
            ],
            [
                'name' => 'Backpack',
                'short_description' => 'Travel backpack 30L',
                'weight' => 200,
                'price' => 550.00,
                'customer_id' => 2,
                'status' => 'packed',
            ],
            [
                'name' => 'Sunglasses',
                'short_description' => 'UV protected sunglasses',
                'weight' => 6.50,
                'price' => 1500,
                'customer_id' => 3,
                'status' => 'packed',
            ],
            [
                'name' => 'Watch',
                'short_description' => 'Casual wristwatch for men',
                'weight' => 50,
                'price' => 900,
                'customer_id' => 3,
                'status' => 'packed',
            ],
        ];

        Product::insert($products);
    }
}
