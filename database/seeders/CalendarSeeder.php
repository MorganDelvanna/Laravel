<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CalendarEvent;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        CalendarEvent::firstOrCreate(
            ['title' => 'Event 1'],
            [
                'start' => now()->toDateTimeString(),
                'end' => now()->addHours(2)->toDateTimeString(),
                'description' => 'This is a calendar event for the admin user.',
                'allDay' => false,
            ]            
        );
        CalendarEvent::firstOrCreate(
            ['title' => 'Event 2'],
            [
                'start' => now()->addDays(2)->toDateTimeString(),
                'end' => now()->addDays(2)->addHours(2)->toDateTimeString(),
                'description' => 'This is a calendar event for the admin user.',
                'allDay' => true,
            ]            
        );
    }
}