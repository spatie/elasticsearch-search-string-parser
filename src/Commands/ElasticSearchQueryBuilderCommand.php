<?php

namespace Spatie\ElasticSearchQueryBuilder\Commands;

use Illuminate\Console\Command;

class ElasticSearchQueryBuilderCommand extends Command
{
    public $signature = 'laravel-elasticsearch-query-builder';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
