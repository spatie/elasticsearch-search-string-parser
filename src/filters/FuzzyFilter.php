<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

class FuzzyFilter implements Filter
{
    public function apply(Builder $builder)
    {
    }

    public function pattern(): string
    {
        return '/title:(?<value>.*)/gi';
    }
}
