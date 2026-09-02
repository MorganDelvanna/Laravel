<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

Route::get('news/list', [NewsController::class, 'list'])->name('news.list');

Route::middleware(['auth', 'role:news'])->prefix('news')->group(function() {
    Route::get('newsList', [NewsController::class, 'newsList'])->name('news.admin');
    Route::post('saveNews', [NewsController::class, 'saveNews'])->name('news.saveNews');
    Route::delete('deleteNews/{id}', [NewsController::class, 'deleteNews'])->name('news.deleteNews');
    Route::post('archiveNews/{id}', [NewsController::class, 'archiveNews'])->name('news.archiveNews');
    // Add more admin routes here
});