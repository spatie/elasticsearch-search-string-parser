<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

interface PatternFilter
{
    public function pattern(): string;

    public function apply(Builder $builder, string $filter, array $values = []);
}
