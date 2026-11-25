<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Weddings', 'slug' => 'weddings', 'sort' => 1],
            ['name' => 'Events', 'slug' => 'events', 'sort' => 2],
            ['name' => 'Football', 'slug' => 'football', 'sort' => 3],
            ['name' => 'Birthdays', 'slug' => 'birthdays', 'sort' => 4],
            ['name' => 'Portraits', 'slug' => 'portraits', 'sort' => 5],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
