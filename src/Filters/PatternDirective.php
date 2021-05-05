<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

abstract class PatternDirective
{
    abstract public function apply(Builder $builder, string $pattern, array $values = []): void;

    abstract public function pattern(): string;

    public function canApply(string $pattern, array $values = []): bool
    {
        return true;
    }

    public function aggregations() {

    }
}
