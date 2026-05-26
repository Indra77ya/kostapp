<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Livewire\SystemSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::middleware('role:owner|developer|admin')->group(function () {
        Route::get('/registrations', function () {
            return view('registrations.index');
        })->name('registrations.index');

        Route::get('/tenants', function () {
            return view('tenants.index');
        })->name('tenants.index');

        Route::get('/check-outs', function () {
            return view('check-outs.index');
        })->name('check-outs.index');

        Route::get('/room-moves', function () {
            return view('room-moves.index');
        })->name('room-moves.index');

        Route::get('/payments', function () {
            return view('payments.index');
        })->name('payments.index');

        Route::get('/payments/confirmation', function () {
            return view('payments.confirmation');
        })->name('payments.confirmation');

        Route::get('/payments/deposits', function () {
            return view('payments.deposits');
        })->name('deposits.index');

        Route::middleware('role:owner|developer')->group(function () {

            Route::get('/locations', function () {
                return view('locations.index');
            })->name('locations.index');

            Route::get('/rooms', function () {
                return view('rooms.index');
            })->name('rooms.index');

            Route::get('/users', function () {
                return view('users.index');
            })->name('users.index');

            Route::get('/facilities', function () {
                return view('facilities.index');
            })->name('facilities.index');

            Route::get('/rules', function () {
                return view('rules.index');
            })->name('rules.index');

            Route::get('/payment-methods', function () {
                return view('payment-methods.index');
            })->name('payment-methods.index');

            Route::get('/settings', function () {
                return view('settings');
            })->name('settings');
        });
    });

    Route::middleware('role:tenant')->group(function () {
        Route::get('/my-payments', function () {
            return view('tenant.payments');
        })->name('tenant.payments');
    });

    // Publicly accessible for all authenticated users but authorized in controller
    Route::get('/registrations/{registration}/invoice', [InvoiceController::class, 'show'])->name('registrations.invoice');
    Route::get('/bills/{bill}/invoice', [InvoiceController::class, 'billInvoice'])->name('bills.invoice');
    Route::get('/payments/{payment}/invoice', [InvoiceController::class, 'paymentInvoice'])->name('payments.invoice');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
