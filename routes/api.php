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

Route::apiResource('comptes', CompteController::class)->only(['index']);
Route::get('/comptes/non-archives', [CompteController::class, 'getNonArchivedComptes']);
Route::get('/comptes/archives', [CompteController::class, 'getArchivedComptes']);
Route::post('/comptes/{id}/archiver', [CompteController::class, 'archiveCompte']);
Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);

Route::get('/test', function () {
    return response()->json(['message' => 'Test route works!']);
});
