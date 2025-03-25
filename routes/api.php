<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix"=> "wishlists"], function() {
    Route::get("/", [WishlistController::class,"index"])->middleware("auth:sanctum");
    Route::post("/", [WishlistController::class,"store"])->middleware("auth:sanctum");
    Route::get("/{wishlist}", [WishlistController::class, "show"])->middleware("auth:sanctum");
    Route::delete("/{wishlist}", [WishlistController::class,"destroy"]);
    Route::delete("/{wishlist}/delete-product", [WishlistController::class, "deleteProduct"]);
    Route::patch("/{wishlist}/update-product", [WishlistController::class, "updateProductQuantity"]);
});


Route::group(["prefix"=> "carts"], function() {
    Route::get("/", [CartController::class,"index"])->middleware("auth:sanctum");
    Route::post("/", [CartController::class,"store"])->middleware("auth:sanctum");
    Route::get("/{cart}", [CartController::class, "show"])->middleware("auth:sanctum");
    Route::delete("/{cart}", [CartController::class,"destroy"]);
    Route::delete("/{cart}/delete-product", [CartController::class, "deleteProduct"]);
    Route::patch("/{cart}/update-product", [CartController::class, "updateProductQuantity"]);
});

Route::group(["prefix"=> "orders"], function() {
    Route::get("/", [OrderController::class,"index"])->middleware("auth:sanctum");
    Route::post("/", [OrderController::class,"store"])->middleware("auth:sanctum");
    Route::get("/{order}", [OrderController::class, "show"])->middleware("auth:sanctum");
});

Route::get("profile", [AuthController::class,"indexs"]);
Route::get("user", [AuthController::class,"index"]);
Route::get("user/{id}", [AuthController::class,"show"]);
Route::patch("user/{id}", [AuthController::class,"update"]);
Route::delete("user/{id}", [AuthController::class,"destroy"]);

Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
Route::get("/user", [AuthController::class, "checkToken"])->middleware("auth:sanctum");
Route::post("/logout", [AuthController::class, "logout"])->middleware("auth:sanctum");

Route::group(["middleware" => "auth:sanctum", 'prefix' => "categories"], function (){
    Route::get("/", [CategoryController::class,"index"]);
    Route::get("/{category}", [CategoryController::class,"show"]);
    Route::post("/", [CategoryController::class,"store"]);
    Route::patch("/{category}", [CategoryController::class,"update"]);
    Route::delete("/{category}", [CategoryController::class,"destroy"]);
});

Route::group(["middleware" => "auth:sanctum", 'prefix' => "products"], function (){
    Route::get("/", [ProductController::class,"index"]);
    Route::get("/{product}", [ProductController::class,"show"]);
    Route::post("/", [ProductController::class,"store"]);
    Route::patch("/{product}", [ProductController::class,"update"]);
    Route::delete("/{product}", [ProductController::class,"destroy"]);
});