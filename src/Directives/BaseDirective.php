<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Builder;

abstract class BaseDirective
{
    abstract public function apply(Builder $builder, string $value): void;

    public function getKey(): string
    {
        return static::class;
    }

    public function transformToSuggestions(array $results): array
    {
        return [];
    }

    public function canApply(string $value): bool
    {
        return true;
    }
}
