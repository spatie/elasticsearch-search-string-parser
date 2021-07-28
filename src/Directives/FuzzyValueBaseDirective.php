<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Aggregations\TermsAggregation;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchQueryBuilder\Queries\MultiMatchQuery;

class FuzzyValueBaseDirective extends BaseDirective
{
    public function __construct(
        protected array $fields,
        protected int|string $fuzziness = 'auto'
    ) {
    }

    public static function forField(string $field): static
    {
        return new static([$field]);
    }

    public static function forFields(string ...$fields): static
    {
        return new static($fields);
    }

    public function setFuzziness(string|int $fuzziness): self
    {
        $this->fuzziness = $fuzziness;

        return $this;
    }

    public function apply(Builder $builder, string $value): void
    {
        if (empty($value)) {
            return;
        }

        $builder->addQuery(MultiMatchQuery::create($value, $this->fields, $this->fuzziness));

        if ($this->useSuggestions === false) {
            return;
        }

        foreach ($this->fields as $field) {
            $builder->addAggregation(TermsAggregation::create("_{$field}_suggestions", "{$field}.keyword"));
        }
    }

    public function transformToSuggestions(array $results): array
    {
        if ($this->useSuggestions === false) {
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
            ->toArray();
    }
}
