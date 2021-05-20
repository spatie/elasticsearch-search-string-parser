<?php

namespace Spatie\ElasticSearchQueryBuilder\Directives;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

abstract class PatternDirective
{
    protected bool $useSuggestions = true;

    abstract public function apply(Builder $builder, string $pattern, array $values = []): void;

    abstract public function pattern(): string;

    public function transformToSuggestions(array $results): array
    {
        return [];
    }

    public function withoutSuggestions(): static
    {
        $this->useSuggestions = false;

        return $this;
    }

    public function canApply(string $pattern, array $values = []): bool
    {
        return true;
    }
}
