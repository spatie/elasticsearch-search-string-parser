<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Builder;

abstract class PatternDirective
{
    protected bool $useSuggestions = false;

    abstract public function apply(Builder $builder, string $pattern, array $values, int $patternOffsetStart, int $patternOffsetEnd): void;

    abstract public function pattern(): string;

    /**
     * The directives key will be used to differentiate between auto-completions
     * for multiple of the same directives. E.g. 2 FuzzeKeyValueDirectives for 2 fields.
     *
     * @return string
     */
    public function getKey(): string
    {
        return static::class;
    }

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
