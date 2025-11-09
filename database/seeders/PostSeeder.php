<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Ambil user pertama
        $categories = Category::all();
        $tags = Tag::all();

        $posts = [
            [
                'title' => 'Getting Started with Laravel 12',
                'excerpt' => 'Learn the basics of Laravel 12 and explore its new features',
                'content' => '<p>Laravel 12 brings exciting new features to the PHP ecosystem. In this article, we\'ll explore the latest additions and improvements that make Laravel even more powerful.</p><p>From enhanced performance to new developer tools, Laravel 12 continues to be the framework of choice for modern PHP development.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'views_count' => rand(100, 1000),
            ],
            [
                'title' => 'Building RESTful APIs with Laravel',
                'excerpt' => 'A comprehensive guide to creating robust APIs',
                'content' => '<p>APIs are the backbone of modern web applications. This guide will walk you through creating a professional RESTful API using Laravel\'s powerful features.</p><p>We\'ll cover authentication, rate limiting, versioning, and best practices for API development.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'views_count' => rand(100, 1000),
            ],
            [
                'title' => 'Mastering Livewire Components',
                'excerpt' => 'Deep dive into Livewire reactive components',
                'content' => '<p>Livewire makes building dynamic interfaces a breeze. In this article, we explore advanced techniques for creating reactive, real-time components without writing JavaScript.</p><p>Learn about component lifecycle, events, and optimization strategies.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'views_count' => rand(100, 1000),
            ],
            [
                'title' => 'Database Optimization Techniques',
                'excerpt' => 'Improve your database performance dramatically',
                'content' => '<p>Database performance is crucial for any application. This article covers indexing strategies, query optimization, and caching techniques to supercharge your database.</p><p>We\'ll also explore N+1 problem solutions and when to use eager loading.</p>',
                'status' => 'published',
                'published_at' => now()->subHours(12),
                'views_count' => rand(50, 500),
            ],
            [
                'title' => 'Understanding Laravel Queues',
                'excerpt' => 'Process jobs asynchronously for better performance',
                'content' => '<p>Queues allow you to defer time-consuming tasks and improve your application\'s response time. Learn how to implement and manage queues effectively in Laravel.</p>',
                'status' => 'published',
                'published_at' => now()->subHours(6),
                'views_count' => rand(50, 300),
            ],
            [
                'title' => 'Draft: Upcoming Features in Laravel',
                'excerpt' => 'A sneak peek at what\'s coming next',
                'content' => '<p>This is a draft post about upcoming Laravel features that we\'re excited about.</p>',
                'status' => 'draft',
                'published_at' => null,
                'views_count' => 0,
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::create([
                'user_id' => $user->id,
                'category_id' => $categories->random()->id,
                'title' => $postData['title'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'],
                'views_count' => $postData['views_count'],
            ]);

            // Attach random tags (2-5 tags per post)
            $randomTags = $tags->random(rand(2, 5));
            $post->tags()->attach($randomTags->pluck('id'));
        }
    }
}
