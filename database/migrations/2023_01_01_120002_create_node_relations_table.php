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
		foreach(['ac_node_relations','ac_published_relations'] as $tableName) {
			Schema::create($tableName,function(Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('source');
				$table->unsignedBigInteger('target');
				$table->string('key');
				$table->dateTime('created_at');
			});
		}

		Schema::table('ac_node_relations',function(Blueprint $table) {
			$table->unique(['source','key','target']);
			$table->index(['target','key']);

			$table->foreign('source')
				->references('id')
				->on('ac_nodes')
				->onUpdate('cascade')
				->onDelete('cascade');

			$table->foreign('target')
				->references('id')
				->on('ac_nodes')
				->onUpdate('cascade')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('ac_node_relations');
		Schema::dropIfExists('ac_published_relations');
	}
};
