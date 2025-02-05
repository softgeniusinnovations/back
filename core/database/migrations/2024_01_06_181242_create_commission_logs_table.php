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
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_id')->nullable();
            $table->unsignedBigInteger('to_id')->nullable();
            $table->unsignedInteger('level')->nullable();
            $table->decimal('commission_amount', 28, 8)->default(0);
            $table->decimal('post_balance', 28, 8)->default(0);
            $table->decimal('trx_amo', 28, 8)->default(0)->comment('Transacted Amount');
            $table->decimal('percent', 11, 2)->default(0.00);
            $table->string('title', 255)->nullable();
            $table->string('type', 40)->nullable();
            $table->string('trx', 255)->nullable();
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
        Schema::dropIfExists('commission_logs');
    }
};
