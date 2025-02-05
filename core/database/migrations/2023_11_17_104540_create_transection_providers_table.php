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
        Schema::create('transection_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('country_code')->default('BD');
            $table->string('file')->nullable();
            $table->string('currency')->default('BDT');
            $table->string('dep_min_am')->default(1);
            $table->string('dep_max_am')->default(1);
            $table->string('with_min_am')->default(1);
            $table->string('with_max_am')->default(1);
            $table->text('note_dep');
            $table->text('note_with');
            $table->boolean('status')->nullable()->default(true);
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
        Schema::dropIfExists('transection_providers');
    }
};
