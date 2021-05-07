<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

abstract class Directive
{
    protected bool $useSuggestions = true;

    abstract public function apply(Builder $builder, string $value): void;

    public function transformToSuggestions(array $results): array
    {
        return [];
    }

    public function withoutSuggestions(): static
    {
        $this->useSuggestions = false;

        return $this;
    }

    public function canApply(string $value): bool
    {
        return true;
    }
}
