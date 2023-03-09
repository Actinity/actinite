<?php
namespace Actinity\Actinite\Commands;

use Actinity\Actinite\Core\Events\SitePublished;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Publish
    extends Command
{
    protected $signature = 'actinite:publish';

    protected $description = 'Publish nodes';

    public function handle()
    {
        DB::statement("
            update ac_nodes set `published_sha` = `current_sha` where is_ready = 1 and deleted_at is null and `published_at` < NOW()
        ");

		$this->setUpNodes();
		$this->setUpRelations();

        DB::statement("drop table if exists ac_published_nodes");
        DB::statement("rename table ac_tmp_nodes to ac_published_nodes");

        DB::statement("drop table if exists ac_published_relations");
        DB::statement("rename table ac_tmp_relations to ac_published_relations");

        Artisan::call('actinite:purge');

		event(new SitePublished);
    }

	private function setUpNodes()
	{
		Schema::dropIfExists('ac_tmp_nodes');
		DB::statement("create table ac_tmp_nodes like ac_nodes");

		DB::statement("insert into ac_tmp_nodes select * from ac_nodes where is_ready = 1 and deleted_at is null and published_at < NOW()");
	}

	private function setUpRelations()
	{
		Schema::dropIfExists('ac_tmp_relations');
		DB::statement("create table ac_tmp_relations like ac_node_relations");

		Schema::table('ac_tmp_relations',function(Blueprint $table) {
			$table->dropUnique('ac_node_relations_source_key_target_unique');
			$table->dropIndex('ac_node_relations_target_key_index');
		});

		DB::statement("insert into ac_tmp_relations select * from ac_node_relations where (select count(id) from ac_tmp_nodes where id = ac_node_relations.source or id = ac_node_relations.target) = 2 ");

		Schema::table('ac_tmp_relations',function(Blueprint $table) {
			$table->index(['source','key']);
			$table->index(['target','key']);
		});
	}
}
