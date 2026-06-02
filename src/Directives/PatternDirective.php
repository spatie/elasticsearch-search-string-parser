<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchStringParser\Suggestion;

abstract class PatternDirective
{
    protected bool $useSuggestions = false;

    abstract public function apply(Builder $builder, string $pattern, array $values, int $patternOffsetStart, int $patternOffsetEnd): void;

    abstract public function pattern(): string;

    /**
     * @return Suggestion[]
     */
    public function transformToSuggestions(array $results): array
    {
        return [];
    }

    public function withSuggestions(): self
    {
        $this->useSuggestions = true;

        return $this;
    }

    public function canApply(string $pattern, array $values = []): bool
    {
        return true;
    }
}
