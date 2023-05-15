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
		Schema::create('ac_assets',function(Blueprint $table) {
			$table->id();
			$table->string('type');
			$table->string('sha');
			$table->string('uuid');
			$table->string('file_name');
			$table->char('extension',20);
			$table->string('mime');
			$table->unsignedBigInteger('size')->nullable();
			$table->integer('width')->nullable();
			$table->integer('height')->nullable();
			$table->integer('duration')->nullable();
			$table->integer('pos_x')->nullable();
			$table->integer('pos_y')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ac_assets');
	}
};
