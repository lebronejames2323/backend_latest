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
        Schema::create('review_product', function (Blueprint $table) {
            $table->unsignedBigInteger("product_id");
            $table->unsignedBigInteger("review_id");
            $table->string("review_text");
            $table->integer("star_rating")->default(0);
            $table->string("extension")->nullable();
            $table->primary(['product_id', 'review_id']);

            $table->foreign("product_id")->references("id")->on("products")->onDelete("cascade");
            $table->foreign("review_id")->references("id")->on("reviews")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_product');
    }
};
