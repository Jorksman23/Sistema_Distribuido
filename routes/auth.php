<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;


Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [LoginController::class, 'register']);

// Obtener usuarios
Route::get('/users', [LoginController::class, 'users']);
