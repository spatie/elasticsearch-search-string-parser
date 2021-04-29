<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MatchQuery;

class FuzzyPatternFilter implements PatternFilter
{
    public function apply(Builder $builder, string $filter, array $values = [])
    {
        $query = new MatchQuery($values['value'], 'category');

        $builder->addQuery($query);
    }

    public function pattern(): string
    {
        return '/filter:(?<value>.*?)(?:$|\s)/i';
    }
}
