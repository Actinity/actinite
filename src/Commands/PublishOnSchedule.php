<?php

namespace Actinity\Actinite\Commands;

use Actinity\Actinite\Core\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PublishOnSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actinite:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled nodes';

    private $publishedSomething = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = Node::query()
            ->where('published_at','<',now())
            ->where('is_ready','=',1)
            ->where('current_sha','!=',DB::raw('coalesce(`published_sha`,1)'))
            ->select('id');

        $query->chunk(50,function($nodes) {
            foreach($nodes as $node) {
                Artisan::call('actinite:publish-one '.$node->id);

                $this->publishedSomething = true;
            }
        });

        if($this->publishedSomething) {
            Artisan::call('actinite:rebuild-tree --published');
        }


        return Command::SUCCESS;
    }
}
