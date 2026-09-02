<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/login', [AdminController::class, 'login'])->name('admin.login');

Route::post('/login', [AdminController::class, 'loginPost'])->name('admin.login.post');

Route::middleware(['auth.basic', 'admin'])
    ->get('/admin/blacklist', [AdminController::class, 'blacklist'])
    ->name('admin.blacklist');

Route::middleware(['auth','admin'])->prefix('admin')->group(function() {
    Route::get('/', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/email-list', [AdminController::class, 'emailList'])->name('admin.emailList');
    // Add more admin routes here
});
