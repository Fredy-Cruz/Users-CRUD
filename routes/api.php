<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//API routes for users
Route::group(["prefix" => "/users"], function () {
    Route::post("/", [UserController::class, "store"]);
    Route::post("/login", [UserController::class, "login"]);
    Route::post("/refresh-token", [UserController::class, "refreshToken"]);

    Route::middleware([AuthMiddleware::class])->group(function () {
        Route::get("/", [UserController::class, "index"])->middleware(AdminMiddleware::class);
        Route::get("/{id}", [UserController::class, "show"])->middleware(AdminMiddleware::class);
        Route::put("/", [UserController::class, "update"]);
        Route::delete("/{id}", [UserController::class, "destroy"])->middleware(AdminMiddleware::class);
    });
});