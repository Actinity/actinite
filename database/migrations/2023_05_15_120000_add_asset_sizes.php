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
		Schema::table('ac_assets',function(Blueprint $table) {
			$table->json('sizes')->nullable()->after('duration');
		});

		\Illuminate\Support\Facades\DB::statement("update ac_assets set sizes = '[]'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ac_assets',function(Blueprint $table) {
			$table->dropColumn('sizes');
		});
	}
};
