<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (protected)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Logout (web)
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Show register & login forms
Route::get('/register', function () {
    return view('auth.register');
})->name('register.form');

Route::get('/login', function () {
    return view('auth.login');
})->name('login.form');

// Handle register & login POST requests
Route::post('/register', [AuthController::class, 'registerWeb'])->name('register');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login');


// for PDF import

Route::get('/upload-pdf', [PdfImportController::class, 'showForm']);
Route::post('/upload-pdf', [PdfImportController::class, 'importFromUpload']);

