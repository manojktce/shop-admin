<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\LoginController;
use App\Http\Controllers\Backend\ForgotPasswordController;
use App\Http\Controllers\Backend\ResetPasswordController;
use App\Http\Controllers\Backend\AccountController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\RoleController;

Route::prefix(env('ADMIN_PATH'))->group(function () {

    Route::middleware(['guest'])->group(function () {
        Route::match(['get', 'post'], '/', [LoginController::class, 'login'])->middleware('throttle:10,1');
        Route::get('forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    });

    Route::middleware(['auth', 'active'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::match(['get', 'put'], 'profile', [AccountController::class, 'profile']);

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::middleware('can:admin-only')->group(function () {
            Route::resource('users', UserController::class)->except(['show', 'destroy']);
            Route::resource('roles', RoleController::class)->except(['show']);
        });
    });

});