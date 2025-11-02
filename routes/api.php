<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\AdminDashboardController;
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


// Route de connexion publique
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth.api_cookie'])->group(function () {
    Route::middleware(['auth.api'])->group(function () {
        Route::middleware(['App\Http\Middleware\LoggingMiddleware'])->group(function () {
            Route::apiResource('comptes', CompteController::class)->only(['index', 'show']);
            Route::patch('/comptes/{id}', [CompteController::class, 'update']);
            Route::middleware(['App\Http\Middleware\IsAdmin'])->group(function () {
                Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard']);
                Route::post('/comptes/{id}/bloquer', [CompteController::class, 'block']);
                Route::post('/comptes/{id}/debloquer', [CompteController::class, 'unblock']);
                Route::get('/admin/comptes/{id}/transactions', [TransactionController::class, 'showByCompte']);
                Route::post('/admin/transactions', [AdminTransactionController::class, 'store']);
            });
            Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);
        });
    });
});
// Route temporaire pour tester sans authentification (supprimée pour sécurité)

// Route séparée pour la création de compte (admin seulement)
Route::middleware(['auth.api', 'role:create-compte'])->middleware(['App\Http\Middleware\LoggingMiddleware'])->post('/comptes', [App\Http\Controllers\CompteCreationController::class, 'store']);

// Route sécurisée pour les tests (seulement en développement ou avec token spécial)
Route::post('/comptes/test', [App\Http\Controllers\CompteCreationController::class, 'storeTest']);

Route::get('/test', function () {
    return response()->json(['message' => 'Test route works!']);
});
