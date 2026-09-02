<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        News::firstOrCreate(
            ['header' => 'Admin News 1'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
        News::firstOrCreate(
            ['header' => 'Admin News 2'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
        News::firstOrCreate(
            ['header' => 'Admin News 3'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
        News::firstOrCreate(
            ['header' => 'Admin News 4'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
        News::firstOrCreate(
            ['header' => 'Admin News 5'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
        News::firstOrCreate(
            ['header' => 'Admin News 6'],
            [
                'publish_date' => now()->toDateString(),
                'description' => 'This is a news article for the admin user.',
                'archived' => false,
            ]
        );
    }
}
