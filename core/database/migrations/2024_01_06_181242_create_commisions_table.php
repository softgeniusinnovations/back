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
        Schema::create('commisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('deposit_id')->nullable();
            $table->unsignedBigInteger('withdraw_id')->nullable();
            $table->string('type', 255)->comment('Deposit, Withdrawal');
            $table->string('commision', 255)->default('0')->comment('%');
            $table->string('amount', 255)->default('0');
            $table->string('final_amount', 255)->default('0');
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
        Schema::dropIfExists('commisions');
    }
};
