<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiController;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:5,1');

// TODO: Add Clean up applicant records

Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);

Route::middleware([
    'auth:sanctum',
    CheckAbilities::class . ':applicants:read',
])->get('/applicants', [ApiController::class, 'getApplicants']);

Route::middleware([
    'auth:sanctum',
    CheckAbilities::class . ':applicants:read',
])->post('/checkup', [ApiController::class, 'checkup']);
