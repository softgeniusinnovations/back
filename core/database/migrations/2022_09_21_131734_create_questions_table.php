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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->string('title', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('locked')->default(0);
            $table->tinyInteger('result')->default(0);
            $table->tinyInteger('refund')->default(0);
            $table->unsignedBigInteger('win_option_id')->default(0);
            $table->tinyInteger('amount_refunded')->default(0);
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
        Schema::dropIfExists('questions');
    }
};
