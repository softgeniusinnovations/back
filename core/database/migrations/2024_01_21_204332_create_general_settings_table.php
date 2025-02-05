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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 40)->nullable();
            $table->string('cur_text', 40)->nullable()->comment('currency text');
            $table->string('cur_sym', 40)->nullable()->comment('currency symbol');
            $table->decimal('single_bet_min_limit', 28, 8);
            $table->decimal('single_bet_max_limit', 28, 8);
            $table->decimal('multi_bet_min_limit', 28, 8)->default(0);
            $table->decimal('multi_bet_max_limit', 28, 8)->default(0);
            $table->tinyInteger('deposit_commission')->unsigned()->default(0);
            $table->tinyInteger('bet_commission')->unsigned()->default(0);
            $table->tinyInteger('win_commission')->unsigned()->default(0);
            $table->string('email_from', 40)->nullable();
            $table->text('email_template')->nullable();
            $table->string('sms_body', 255)->nullable();
            $table->string('sms_from', 255)->nullable();
            $table->string('base_color', 40)->nullable();
            $table->text('mail_config')->comment('email configuration')->nullable();
            $table->text('sms_config')->nullable();
            $table->text('global_shortcodes')->nullable();
            $table->tinyInteger('kv')->default(0);
            $table->tinyInteger('ev')->default(0)->comment('email verification, 0 - dont check, 1 - check');
            $table->tinyInteger('en')->default(0)->comment('email notification, 0 - dont send, 1 - send');
            $table->tinyInteger('sv')->default(0)->comment('mobile verification, 0 - dont check, 1 - check');
            $table->tinyInteger('sn')->default(0)->comment('sms notification, 0 - dont send, 1 - send');
            $table->tinyInteger('force_ssl')->default(0);
            $table->tinyInteger('maintenance_mode')->default(0);
            $table->tinyInteger('secure_password')->default(0);
            $table->tinyInteger('agree')->default(0);
            $table->tinyInteger('multi_language')->unsigned()->default(1);
            $table->tinyInteger('registration')->default(0)->comment('0: Off, 1: On');
            $table->string('active_template', 40)->nullable();
            $table->text('system_info')->nullable();
            $table->tinyInteger('system_customized')->default(0);
            $table->dateTime('last_win_cron')->nullable();
            $table->dateTime('last_lose_cron')->nullable();
            $table->dateTime('last_refund_cron')->nullable();
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
        Schema::dropIfExists('general_settings');
    }
};
