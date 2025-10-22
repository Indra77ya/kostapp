<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::resource('bookings', BookingController::class);
Route::post('bookings/{booking}/confirm', [BookingController::class,'confirm'])->name('bookings.confirm');
Route::resource('stays', StayController::class)->only(['index','show','store']);
Route::post('stays/{stay}/checkout', [StayController::class,'checkout'])->name('stays.checkout');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
