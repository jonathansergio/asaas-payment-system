<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

Route::get('/checkout', [CheckoutController::class, 'showForm']);
Route::post('/checkout', [CheckoutController::class, 'process']);
