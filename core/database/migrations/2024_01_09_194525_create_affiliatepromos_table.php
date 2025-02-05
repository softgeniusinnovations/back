<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliatepromos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affliate_user_id')->nullable();
            $table->unsignedBigInteger('better_user_id')->nullable();
            $table->unsignedBigInteger('promo_id')->nullable();

            $table->foreign('affliate_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('better_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('promo_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliatepromos');
    }
};
