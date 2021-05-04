<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

abstract class Directive
{
    abstract public function apply(Builder $builder, string $value): void;

    public function canApply(string $value): bool
    {
        return true;
    }
}
