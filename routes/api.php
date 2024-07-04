<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/accounts')->group(function() {
    Route::post('/search', [App\Http\Controllers\Api\AccountController::class, 'search'])->name('api.accounts.search');
});
