<?php
namespace Actinity\Actinite\Commands;

use Illuminate\Console\Command;
use Actinity\Actinite\Jobs\Publish as PublishJob;

class Publish
    extends Command
{
    protected $signature = 'actinite:publish';

    protected $description = 'Publish nodes';

    public function handle()
    {
        dispatch_sync(new PublishJob(1));
    }
}
