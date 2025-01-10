<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 */

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/password/reset-link', [AuthController::class, 'resetPassword']);
Route::get('/password/reset-token', function (Request $request) {
    return response()->json(['token' => $request->query('token')]);
})->name('password.reset');
Route::post('/password/update', [AuthController::class, 'updatePassword']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUserAuthenticated']);
Route::middleware('auth:sanctum')->get('/users', [AuthController::class, 'getUsers']);
Route::middleware('auth:sanctum')->delete('/user/{id}', [AuthController::class, 'deleteUser']);
Route::middleware('auth:sanctum')->put('/user/profile', [ProfileController::class, 'updateProfile']);



