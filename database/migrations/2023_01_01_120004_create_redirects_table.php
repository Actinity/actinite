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
		Schema::create('ac_redirects',function(Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('node_id');
			$table->text('path');
			$table->string('sha_path');
			$table->dateTime('last_at')->nullable();
			$table->timestamps();

			$table->foreign('node_id')
				->references('id')
				->on('ac_nodes')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->index('sha_path');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ac_redirects');
	}
};
