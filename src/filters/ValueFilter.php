<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

interface ValueFilter
{
    public function apply(Builder $builder, string $value);
}
