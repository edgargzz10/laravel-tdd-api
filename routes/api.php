<?php

use App\Enums\Roles;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PlateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\MenuController as PublicMenuController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UpdatePasswordController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/users', [RegisterController::class, 'store']);
Route::put('/profile', [ProfileController::class, 'update']);
Route::put('/password', [UpdatePasswordController::class, 'update']);
Route::post('/reset-password', [ResetPasswordController::class, 'send']);
Route::put('/reset-password', [ResetPasswordController::class, 'reset_password']);

Route::get('menus/{menu:id}',[PublicMenuController::class, 'show'])->name('public.menu.show');


Route::middleware('auth:api')->group(function () {
    # Restaurants
    Route::middleware('role:'.Roles::ADMIN->value)->apiResource('users', UserController::class)->only('destroy');
    Route::apiResource('restaurants', RestaurantController::class);

    Route::middleware('can:view,restaurant')
        ->as('restaurants.')
        ->prefix('restaurants/{restaurant:id}')
        ->scopeBindings()
        ->group(function () {

        Route::apiResource('plates', PlateController::class);

        Route::apiResource('menus', MenuController::class);
    });
});
