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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->string('bet_number', 40)->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->tinyInteger('type')->unsigned()->default(1)->comment('Single: 1, Multi: 2');
            $table->decimal('stake_amount', 28, 8)->default(0);
            $table->decimal('return_amount', 28, 8)->default(0);
            $table->tinyInteger('status')->unsigned()->default(0)->comment('Win: 1, Pending: 2, Lose: 3, Refunded: 4');
            $table->tinyInteger('amount_returned')->unsigned()->default(0);
            $table->datetime('result_time')->nullable();
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
        Schema::dropIfExists('bets');
    }
};
