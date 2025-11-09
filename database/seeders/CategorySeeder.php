<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'description' => 'Articles about technology, programming, and software development',
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'description' => 'Business strategies, entrepreneurship, and startup insights',
                'is_active' => true,
            ],
            [
                'name' => 'Lifestyle',
                'description' => 'Lifestyle, health, and personal development content',
                'is_active' => true,
            ],
            [
                'name' => 'Travel',
                'description' => 'Travel guides, tips, and destination recommendations',
                'is_active' => true,
            ],
            [
                'name' => 'Food',
                'description' => 'Recipes, restaurant reviews, and culinary adventures',
                'is_active' => true,
            ],
            [
                'name' => 'Education',
                'description' => 'Learning resources, tutorials, and educational content',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
