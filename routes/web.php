<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});

// --- Auth-protected routes
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD (Controller → view('dashboard.index'))
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Bookings
    Route::resource('bookings', BookingController::class);
    Route::post('bookings/{booking}/confirm', [BookingController::class,'confirm'])->name('bookings.confirm');

    // Stays
    Route::resource('stays', StayController::class)->only(['index','show','store']);
    Route::post('stays/{stay}/checkout', [StayController::class,'checkout'])->name('stays.checkout');

    // Invoices & Payment
    Route::resource('invoices', InvoiceController::class)->only(['index','show','create','store']);
    Route::post('invoices/{invoice}/pay', [PaymentController::class,'store'])->name('invoices.pay');

    // Reports
    Route::get('reports/availability', [ReportController::class, 'availability'])->name('reports.availability');
    Route::get('reports/profit-loss',  [ReportController::class, 'profitLoss'])->name('reports.profit_loss');
    Route::get('reports/balance-sheet',[ReportController::class, 'balanceSheet'])->name('reports.balance_sheet');

    // Tickets
    Route::resource('tickets', TicketController::class)->only(['index','store','update']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
