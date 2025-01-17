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
        Schema::create('app_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->text('data');
            $table->string('server')->nullable();
            $table->string('user_id')->nullable();
            $table->unsignedBigInteger('impersonated_by')->nullable();
            $table->string('eventable_type')->nullable();
            $table->integer('eventable_id')->nullable();

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
        Schema::dropIfExists('app_events');
    }
};
