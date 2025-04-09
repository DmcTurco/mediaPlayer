<?php

use App\Http\Controllers\Api\ApiClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [ApiClientController::class, 'login']);

Route::get('/check-connection', function () {
    return response()->json(['status' => 'ok']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('logout', [ApiClientController::class, 'logout']);
    Route::get('profile', [ApiClientController::class, 'profile']);
});
