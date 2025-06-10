<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("product_id");
            $table->unsignedBigInteger("cart_id");
            $table->unsignedBigInteger("variation_id")->nullable();
            $table->integer("quantity");
            $table->decimal("price", 12, 2);

            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
            $table->foreign("cart_id")->references("id")->on("carts")->onDelete("cascade");
            $table->foreign("variation_id")->references("id")->on("variations")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_product');
    }
};