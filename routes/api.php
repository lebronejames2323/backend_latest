<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VariationController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
Route::post("/logout", [AuthController::class, "logout"])->middleware("auth:sanctum");

Route::group(["prefix" => "user", "middleware" => "auth:sanctum"], function () {
    Route::get("/user-count", [AuthController::class, "getNewUsersCount"]);
    Route::get("/", [AuthController::class,"index"]);
    Route::get("/{id}", [AuthController::class,"show"]);
    Route::patch("/{id}", [AuthController::class,"update"]);
    Route::delete("/{id}", [AuthController::class,"destroy"]);
    Route::get("/", [AuthController::class, "checkToken"]);

    Route::get("/{user}/addresses", [AddressController::class, "index"]);
    Route::post("/{user}/addresses", [AddressController::class, "store"]);
    Route::delete("/addresses/{addressId}", [AddressController::class, "destroy"]);
});


Route::group(["prefix"=> "wishlists", "middleware" => "auth:sanctum"], function() {
    Route::get("/", [WishlistController::class,"index"]);
    Route::post("/", [WishlistController::class,"store"]);
    Route::get("/{wishlist}", [WishlistController::class, "show"]);
    Route::delete("/{wishlist}/delete-product", [WishlistController::class, "deleteProduct"]);
    Route::patch("/{wishlist}/update-product", [WishlistController::class, "updateProductQuantity"]);
});


Route::group(["prefix" => "carts", "middleware" => "auth:sanctum"], function () {
    Route::get("/", [CartController::class, "index"]);
    Route::post("/", [CartController::class, "store"]);
    Route::get("/{cart}", [CartController::class, "show"]);
    Route::delete("/{cart}", [CartController::class,"destroy"]);
    Route::delete("/{cart}/delete-product", [CartController::class, "deleteProduct"]);
    Route::patch("/{cart}/update-product", [CartController::class, "updateProductQuantity"]);
});


Route::group(["prefix" => "orders", "middleware" => "auth:sanctum"], function () {
    Route::get("/", [OrderController::class, "index"]);
    Route::post("/", [OrderController::class, "store"]);
    Route::get("/{order}", [OrderController::class, "show"]);
    Route::get('/admin/orders', [OrderController::class, 'getAllOrders']);
    Route::get('/admin/recent-orders', [OrderController::class, 'getRecentOrders']);
    Route::patch("/{orderId}/status", [OrderController::class, "updateOrderStatus"]);
    Route::delete("/{orderId}/cancel-order", [OrderController::class, "cancelOrder"]);
});


Route::group(['prefix' => "categories"], function (){
    Route::get("/with-sold-count", [CategoryController::class,"categoriesPurchaseCount"]);
    Route::get("/", [CategoryController::class,"index"]);
    Route::get("/{category}", [CategoryController::class,"show"]);
    Route::post("/", [CategoryController::class,"store"])->middleware("auth:sanctum");
    Route::patch("/{category}", [CategoryController::class,"update"])->middleware("auth:sanctum");
    Route::delete("/{categoryId}", [CategoryController::class,"destroy"])->middleware("auth:sanctum");
});


Route::group(['prefix' => "products"], function (){
    Route::get('/sales-data', [ProductController::class, 'getSalesData']);
    Route::get("/get-all", [ProductController::class,"allProducts"]);
    Route::get("/", [ProductController::class,"index"]);
    Route::get("/{productId}", [ProductController::class,"show"]);
    Route::post("/", [ProductController::class,"store"])->middleware("auth:sanctum");
    Route::patch("/{product}", [ProductController::class,"update"])->middleware("auth:sanctum");
    Route::delete("/{productId}", [ProductController::class,"destroy"])->middleware("auth:sanctum");
});

Route::group(["prefix" => "notifications", "middleware" => "auth:sanctum"], function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/mark-read', [NotificationController::class, 'markAllAsRead']);
});


Route::group(["prefix" => "variations", "middleware" => "auth:sanctum"], function () {
    Route::get('/get', [VariationController::class, 'indexPagination']);
    Route::post('/post', [VariationController::class, 'store']);
    Route::patch('/update/{id}', [VariationController::class, 'update']);
    Route::delete('/delete/{id}', [VariationController::class, 'destroy']);
});

Route::get('/variations', [VariationController::class, 'index']);


Route::get('/reviews', [ReviewController::class, 'getAllReviews']);
Route::get('/product-ratings', [ReviewController::class, 'getProductRating']);
Route::post('/reviews', [ReviewController::class, 'store'])->middleware("auth:sanctum");

Route::get("/featured-products", [ProductController::class,"featured"]);
Route::get("/recommended-products", [ProductController::class, "recommended"]);