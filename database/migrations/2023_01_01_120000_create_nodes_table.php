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
		foreach(['ac_nodes','ac_published_nodes'] as $tableName) {
			Schema::create($tableName,function(Blueprint $table) {
				$table->id();
				$table->string('type');
				$table->string('name');
				$table->string('slug');
				$table->string('path');
				$table->string('page_template')->nullable();
				$table->unsignedBigInteger('parent_id')->nullable();
				$table->integer('ordering')->default(0);
				$table->bigInteger('lft')->default(0);
				$table->bigInteger('rgt')->default(0);
				$table->json('data')->nullable();
				$table->json('asset_cache');
				$table->json('child_order')->nullable();
				$table->boolean('is_protected')->default(0);
				$table->boolean('is_locked')->default(0);
				$table->boolean('is_ready')->default(1);
				$table->dateTime('published_at')->nullable();
				$table->string('current_sha')->nullable();
				$table->string('published_sha')->nullable();
				$table->timestamps();
				$table->softDeletes();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ac_nodes');
		Schema::dropIfExists('ac_published_nodes');
	}
};
