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
        Schema::create('bet_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bet_id')->default(0);
            $table->integer('question_id')->default(0);
            $table->unsignedBigInteger('option_id')->default(0);
            $table->decimal('odds', 5, 2)->default(0.00);
            $table->tinyInteger('status')->unsigned()->default(0)->comment('Win: 1, Pending: 2, Lose: 3, Refunded: 4');
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
        Schema::dropIfExists('bet_details');
    }
};
