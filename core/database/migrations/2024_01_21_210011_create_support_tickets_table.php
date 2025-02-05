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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('name', 40)->nullable();
            $table->string('email', 40)->nullable();
            $table->string('ticket', 40)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('trx_no', 255)->nullable();
            $table->string('trx_date', 255)->nullable();
            $table->bigInteger('bet_id')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Open, 1: Answered, 2: Replied, 3: Closed');
            $table->tinyInteger('priority')->default(0)->comment('1 = Low, 2 = Medium, 3 = High');
            $table->datetime('last_reply')->nullable();
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
        Schema::dropIfExists('support_tickets');
    }
};
