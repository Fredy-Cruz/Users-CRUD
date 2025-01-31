<?php

use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//API routes for users
Route::group(["prefix"=> "/users"], function () {
    Route::get("/", [UserController::class, "index"]);
    Route::post("/", [UserController::class,"store"]);
    Route::get("/{id}", [UserController::class, "show"]);
    Route::put("/{id}", [UserController::class, "update"]);
    Route::delete("/{id}", [UserController::class, "destroy"]);
    Route::post("/login", [UserController::class,"login"])->middleware(AuthMiddleware::class);
});
