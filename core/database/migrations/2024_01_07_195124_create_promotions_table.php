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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('details')->nullable();
            $table->string('image')->nullable();
            $table->string('promo_code')->nullable();
            $table->string('promo_percentage')->nullable();
            $table->string('promo_amount')->nullable();
            $table->string('is_admin_approved')->nullable()->default(0);
            $table->text('admin_comment')->nullable();
            $table->string('learn_more_link')->nullable();
            $table->string('status')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
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
        Schema::dropIfExists('promotions');
    }
};
