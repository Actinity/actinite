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
		Schema::create('ac_node_snapshots',function(Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('node_id');
			$table->unsignedBigInteger('user_id')->nullable();
			$table->string('type');
			$table->string('name');
			$table->string('slug');
			$table->string('page_template')->nullable();
			$table->unsignedBigInteger('parent_id')->nullable();
			$table->integer('ordering')->default(0);
			$table->json('data')->nullable();
			$table->json('child_order')->nullable();
			$table->boolean('is_protected')->default(0);
			$table->boolean('is_locked')->default(0);
			$table->boolean('is_ready')->default(1);
			$table->dateTime('published_at')->nullable();
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
		Schema::dropIfExists('ac_node_snapshots');
	}
};
