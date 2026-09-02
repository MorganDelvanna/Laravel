<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;

Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

Route::middleware(['auth', 'role:calendar'])->prefix('calendar')->group(function() {
    Route::get('eventList', [CalendarController::class, 'eventList'])->name('calendar.admin');
    Route::post('saveEvent', [CalendarController::class, 'saveEvent'])->name('calendar.saveEvent');
    Route::delete('deleteEvent/{id}', [CalendarController::class, 'deleteEvent'])->name('calendar.deleteEvent');
    // Add more admin routes here
});
