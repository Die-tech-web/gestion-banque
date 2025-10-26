<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AuthController;
use App\Models\User;

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

// Route de connexion publique
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::middleware(['auth.api_cookie'])->group(function () {
        Route::middleware(['App\Http\Middleware\LoggingMiddleware'])->group(function () {
            Route::apiResource('comptes', CompteController::class)->only(['index', 'show']);
            Route::get('/comptes/non-archives', [CompteController::class, 'getNonArchivedComptes']);
            Route::get('/comptes/archives', [CompteController::class, 'getArchivedComptes']);
            Route::post('/comptes/{id}/archiver', [CompteController::class, 'archiveCompte']);
            Route::post('/comptes/{id}/bloquer', [CompteController::class, 'block']);
            Route::post('/comptes/{id}/debloquer', [CompteController::class, 'unblock']);
            Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);
        });
    });
});

// Route séparée pour la création de compte (admin seulement)
Route::middleware(['auth:api'])->middleware(['auth.api_cookie'])->middleware(['App\Http\Middleware\LoggingMiddleware'])->post('/comptes', [App\Http\Controllers\CompteCreationController::class, 'store']);

// Route pour créer des comptes sans authentification (pour les tests)
Route::post('/comptes/test', [App\Http\Controllers\CompteCreationController::class, 'storeTest']);

Route::get('/test', function () {
    return response()->json(['message' => 'Test route works!']);
});
