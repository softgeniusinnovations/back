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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->nullable();
            $table->string('email', 40)->nullable();
            $table->string('username', 40)->nullable();
            $table->string('country_code', 50)->nullable();
            $table->string('currency', 50)->default('BDT');
            $table->string('phone', 50)->nullable();
            $table->string('deposit_commission', 50)->default('0');
            $table->string('withdraw_commission', 50)->default('0');
            $table->double('balance', 28, 8)->default(0);
            $table->string('ver_code', 50)->nullable();
            $table->tinyInteger('type')->nullable()->comment('0=super, 1=agent, 2=cash-agent, 3=mob-agent');
            $table->text('address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('remember_token', 255)->nullable();
            $table->string('bot_token', 255)->nullable();
            $table->string('bot_username', 255)->nullable();
            $table->string('channel_id', 255)->nullable();
            $table->string('telegram_link', 255)->nullable();
            $table->integer('is_login')->default(0);
            $table->timestamps();
            $table->tinyInteger('status')->default(1);

            $table->unique(['email', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
