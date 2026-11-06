<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\LoginController;
use App\Http\Controllers\Backend\ForgotPasswordController;
use App\Http\Controllers\Backend\ResetPasswordController;
use App\Http\Controllers\Backend\AccountController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\RoleController;

Route::prefix(env('ADMIN_PATH'))->group(function () {

    Route::middleware(['guest'])->group(function () {
        Route::match(['get', 'post'], '/', [LoginController::class, 'login']);
        Route::get('forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
    });

    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::match(['get', 'put'], 'profile', [AccountController::class, 'profile']);

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
    });

});