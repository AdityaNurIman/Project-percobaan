<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $categories = [
            'Vitae Sunt' => 'Similique odit et officiis nihil.',
            'Cum Nihil' => 'Sunt porro ratione et impedit veritatis et culpa.',
            'Enim Nihil' => 'Qui et consequatur praesentium perspiciatis.',
            'Laborum Eos' => 'Qui est a omnis magnam qui veritatis qui.',
            'Nostrum Et' => 'Ea quos qui amet odit debitis amet.',
            'Qui Eius' => 'Et eos qui qui quibusdam occaecati.',
            'Quis Illum' => 'Qui vel saepe ullam explicabo.',
            'Ut Est' => 'Fugiat esse rerum atque quia.',
        ];

        foreach ($categories as $name => $description) {
            Category::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'description' => $description,
            ]);
        }

        Post::create([
            'title' => 'Blog Post Title',
            'text' => 'A short description of the blog post goes here...',
            'category_id' => 1,
        ]);

        Post::create([
            'title' => 'Another Blog Post',
            'text' => 'Another short description of a blog post...',
            'category_id' => 1,
        ]);
    }
}
