<?php

namespace Spatie\ElasticSearchQueryBuilder\Directives;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;

class FuzzyKeyValuePatternDirective extends PatternDirective
{
    public function __construct(protected string $key, protected array $fields)
    {
    }

    public static function forField(string $key, string $field): static
    {
        return new static($key, [$field]);
    }

    public static function forFields(string $key, string ...$fields): static
    {
        return new static($key, $fields);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function apply(Builder $builder, string $pattern, array $values = []): void
    {
        $builder->addQuery(MultiMatchQuery::create($values['value'], $this->fields));

        if ($this->useSuggestions === false) {
            return;
        }

        foreach ($this->fields as $field) {
            $builder->addAggregation(TermsAggregation::create("_{$field}_suggestions", "{$field}.keyword"));
        }
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<value>.*?)(?:$|\s)/i";
    }

    public function transformToSuggestions(array $results): array
    {
        if($this->useSuggestions === false){
            return [];
        }

        $validAggregations = array_map(
            fn(string $field) => "_{$field}_suggestions",
            $this->fields
        );

        return collect($results['aggregations'] ?? [])
            ->filter(fn(array $aggregation, string $name) => in_array($name, $validAggregations))
            ->flatMap(fn(array $aggregation) => array_map(
                fn(array $bucket) => $bucket['key'],
                $aggregation['buckets']
            ))
            ->sort()
            ->toArray();
    }
}
