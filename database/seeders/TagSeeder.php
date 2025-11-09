<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Laravel', 'color' => '#f56565'],
            ['name' => 'PHP', 'color' => '#667eea'],
            ['name' => 'JavaScript', 'color' => '#f6ad55'],
            ['name' => 'Vue.js', 'color' => '#48bb78'],
            ['name' => 'React', 'color' => '#4299e1'],
            ['name' => 'Tailwind CSS', 'color' => '#38b2ac'],
            ['name' => 'DevOps', 'color' => '#ed64a6'],
            ['name' => 'AI & Machine Learning', 'color' => '#9f7aea'],
            ['name' => 'Web Development', 'color' => '#4fd1c5'],
            ['name' => 'Mobile Apps', 'color' => '#fc8181'],
            ['name' => 'Database', 'color' => '#f687b3'],
            ['name' => 'Security', 'color' => '#68d391'],
            ['name' => 'Cloud Computing', 'color' => '#63b3ed'],
            ['name' => 'Career Tips', 'color' => '#fbd38d'],
            ['name' => 'Tutorial', 'color' => '#b794f4'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
