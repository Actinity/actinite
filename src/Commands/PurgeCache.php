<?php
namespace Actinity\Actinite\Commands;

use Actinity\Actinite\Services\TypeService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PurgeCache
    extends Command
{
    protected $signature = 'actinite:purge';

    protected $description = 'Clear cache';

    public function handle()
    {
        $fs = new Filesystem();
        $fs->cleanDirectory('storage/app/cache');

        TypeService::clearCache();
    }
}
