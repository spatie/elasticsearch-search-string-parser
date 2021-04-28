<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

interface Filter
{
    public function pattern(): string;

    public function apply(Builder $builder);
}
