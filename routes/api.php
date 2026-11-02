<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Applicant;
use App\Http\Controllers\ApiAuthController;
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

Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);

Route::middleware([
    'auth:sanctum',
    CheckAbilities::class . ':applicants:read',
])->get('/applicants', function (Request $request) {
    $collectedRecords = [];
    $res = Applicant::where('processed', 0)->whereNotNull('transaction_id')->get();
    foreach ($res as $applicant) {
        $collectedRecords[] = encrypt($applicant);
    }

    return response()->json($collectedRecords);
});
