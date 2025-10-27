<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\ScopeController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/v1/{name}/documentation', function ($name) {
    Config::set('l5-swagger.defaults.paths.base', '/api/v1/' . $name);
    return redirect('/api/documentation');
});

// OAuth routes
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
Route::get('/oauth/authorize', [AccessTokenController::class, 'authorize']);
Route::post('/oauth/authorize', [AccessTokenController::class, 'approve']);
Route::delete('/oauth/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy']);
Route::get('/oauth/clients', [ClientController::class, 'index']);
Route::post('/oauth/clients', [ClientController::class, 'store']);
Route::put('/oauth/clients/{client_id}', [ClientController::class, 'update']);
Route::delete('/oauth/clients/{client_id}', [ClientController::class, 'destroy']);
Route::get('/oauth/scopes', [ScopeController::class, 'index']);
Route::get('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'index']);
Route::post('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'store']);
Route::delete('/oauth/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy']);
Route::get('/oauth/transient', [TransientTokenController::class, 'index']);


// use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Config;

// /*
// |--------------------------------------------------------------------------
// | Web Routes
// |--------------------------------------------------------------------------
// |
// | Ici, tu peux définir les routes web de ton application.
// | Ces routes sont chargées par le RouteServiceProvider et
// | assignées au groupe "web" middleware.
// |
// */

// Route::get('/', function () {
//     return view('welcome');
// });

// // ✅ Swagger avec préfixe dynamique (ex: /api/v1/die.niang/documentation)
// Route::get('/api/v1/{name}/documentation', function ($name) {
//     // On met à jour dynamiquement le chemin de base de l’API dans Swagger
//     Config::set('l5-swagger.defaults.paths.base', '/api/v1/' . $name);

//     // ✅ On redirige vers la bonne route Swagger qui existe déjà
//     // En local ou en production, L5-Swagger sert son interface sur /api/documentation
//     return redirect('/api/documentation');
// });

// // ✅ Optionnel : raccourci direct pour ton cas spécifique (rend plus clair sur Render)
// Route::get('/api/v1/die.niang/documentation', function () {
//     return redirect('/api/documentation');
// });

