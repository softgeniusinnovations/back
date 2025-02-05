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
        Schema::create('affiliatecommissiontransaction', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('affiliate_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('promo_id')->nullable();
            $table->string('result')->nullable()->comment('1 = Earn amount, 2 = loss amount');
            $table->string('currency_rate')->nullable();
            $table->string('currency_symbol')->nullable();
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
        Schema::dropIfExists('affiliatecommissiontransaction');
    }
};
