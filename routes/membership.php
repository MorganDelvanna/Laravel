<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;

Route::get('/membership/{applicationType}', [App\Http\Controllers\MembershipController::class, 'memberForm'])->name('membership.form');
Route::post('/checkout', [App\Http\Controllers\MembershipController::class, 'checkout'])->name('membership.checkout');
Route::get('/success/{sessionId}', [App\Http\Controllers\MembershipController::class, 'success'])->name('membership.success');
Route::get('/canceled/{sessionId}', [App\Http\Controllers\MembershipController::class, 'canceled'])->name('membership.canceled');