<?php

use App\MyApp;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix(MyApp::ADMINS_SUBDIR)->middleware('auth:admin')->name('name.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.home');
    })->withoutMiddleware('auth:admin');
});
