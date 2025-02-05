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
        Schema::create('agent_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('currency')->default('BDT');
            $table->string('trx')->unique()->nullable();
            $table->string('amount')->default(0);
            $table->string('rate')->default(1);
            $table->string('deposit_trx')->unique()->nullable();
            $table->string('depositor_account')->nullable();
            $table->string('deposit_currency', 255)->nullable();
            $table->string('file', 255)->nullable();
            $table->string('feedback')->nullable();
            $table->boolean('status')->default('1')->comment('1=pending 2=success 3=rejected');
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
        Schema::dropIfExists('agent_deposits');
    }
};
