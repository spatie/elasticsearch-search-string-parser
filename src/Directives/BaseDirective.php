<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Builder;

abstract class BaseDirective
{
    protected bool $useSuggestions = false;

    abstract public function apply(Builder $builder, string $value): void;

    public function transformToSuggestions(array $results): array
    {
        return [];
    }

    public function canApply(string $value): bool
    {
        return true;
    }

    public function withSuggestions(): self
    {
        $this->useSuggestions = true;

        return $this;
    }
}
