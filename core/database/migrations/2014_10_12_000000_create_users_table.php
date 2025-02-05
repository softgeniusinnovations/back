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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('country_code')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('mobile')->nullable();
            $table->date('dob')->nullable();
            $table->unsignedBigInteger('ref_by')->default(0);
            $table->string('referral_code')->nullable();
            $table->string('currency')->default(0);
            $table->decimal('balance', 28, 8)->default(0);
            $table->decimal('bonus_account', 20, 6)->default(0);
            $table->string('password', 255);
            $table->text('address')->comment('contains full address');
            $table->tinyInteger('status')->default(1)->comment('0: banned, 1: active');
            $table->text('kyc_data')->nullable();
            $table->tinyInteger('kv')->default(0)->comment('0: KYC Unverified, 2: KYC pending, 1: KYC verified');
            $table->tinyInteger('ev')->default(0)->comment('0: email unverified, 1: email verified');
            $table->tinyInteger('sv')->default(0)->comment('0: mobile unverified, 1: mobile verified');
            $table->tinyInteger('profile_complete')->default(0);
            $table->string('ver_code', 40)->nullable()->comment('stores verification code');
            $table->datetime('ver_code_send_at')->nullable()->comment('verification send time');
            $table->tinyInteger('ts')->default(0)->comment('0: 2fa off, 1: 2fa on');
            $table->tinyInteger('tv')->default(1)->comment('0: 2fa unverified, 1: 2fa verified');
            $table->string('tsc', 255)->nullable();
            $table->string('ban_reason', 255)->nullable();
            $table->integer('is_affiliate')->default(0);
            $table->string('profile_mode', 255)->nullable();
            $table->string('youtube_link', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('one_time_pass', 255)->nullable();
            $table->string('remember_token', 255)->nullable();
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
        Schema::dropIfExists('users');
    }
};
