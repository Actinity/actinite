<?php

namespace Actinity\Actinite\Jobs;

use Actinity\Actinite\Core\Events\SitePublished;
use Actinity\Actinite\Services\TreeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Publish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $root;

    public function middleware()
    {
        return [(new WithoutOverlapping())->expireAfter(60)];
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($root = 1)
    {
        $this->root = $root;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->root = DB::table('ac_nodes')->find($this->root);

        DB::table('ac_nodes')
            ->where('is_ready',1)
            ->whereNull('deleted_at')
            ->where('lft','>=',$this->root->lft)
            ->where('rgt','<=',$this->root->rgt)
            ->update([
                'published_sha' => DB::raw('current_sha'),
                'published_at' => DB::raw('coalesce(published_at,now())')
            ]);

        $this->setUpNodes();
        $this->setUpRelations();

        DB::statement("drop table if exists ac_published_nodes");
        DB::statement("rename table ac_tmp_nodes to ac_published_nodes");

        DB::statement("drop table if exists ac_published_relations");
        DB::statement("rename table ac_tmp_relations to ac_published_relations");

        Artisan::call('actinite:purge');

        event(new SitePublished);

        dispatch((new RebuildTree(true,$this->root->id,$this->root->lft))->delay(new \DateInterval('PT10S')));
    }

    private function setUpNodes()
    {
        Schema::dropIfExists('ac_tmp_nodes');
        DB::statement("create table ac_tmp_nodes like ac_nodes");

        DB::statement("insert into ac_tmp_nodes select * from ac_nodes where is_ready = 1 and deleted_at is null and published_at <= NOW()");
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
