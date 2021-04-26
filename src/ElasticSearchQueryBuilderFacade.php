<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\ElasticSearchQueryBuilder\ElasticSearchQueryBuilder
 */
class ElasticSearchQueryBuilderFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-elasticsearch-query-builder';
    }
}
