<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompteController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/v1/comptes', [CompteController::class, 'index']);
Route::get('/v1/comptes/non-archives', [CompteController::class, 'getNonArchivedComptes']);
Route::get('/v1/comptes/archives', [CompteController::class, 'getArchivedComptes']);

Route::get('/v1/test', function () {
    return response()->json(['message' => 'Test route works!']);
});
