<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);

    // CRUD
    Route::get('users', [AuthController::class, 'index']);          // all users
    Route::get('users/{id}', [AuthController::class, 'show']);      // single user
    Route::put('users/{id}', [AuthController::class, 'update']);    // update user
    Route::delete('users/{id}', [AuthController::class, 'destroy']); // delete user
});

