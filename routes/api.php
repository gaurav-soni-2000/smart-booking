<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AdminController;

Route::get('/services', [BookingController::class, 'services']);
Route::get('/slots', [BookingController::class, 'slots']);
Route::post('/book', [BookingController::class, 'book']);

Route::get('/admin/rules', [AdminController::class, 'listRules']);
Route::post('/admin/rules', [AdminController::class, 'createRule']);
Route::delete('/admin/rules/{id}', [AdminController::class, 'deleteRule']);
