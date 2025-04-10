<?php

use App\MyApp;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin as Admin;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix(MyApp::ADMINS_SUBDIR)->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.home');
    })->withoutMiddleware('auth:admin');

    Route::get('/home', [Admin\AdminController::class, 'index'])->name('home');
    Route::resource('video', Admin\VideoController::class);
});
