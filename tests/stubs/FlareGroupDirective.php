<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\stubs;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\GroupDirective;

class FlareGroupDirective extends GroupDirective
{
    protected array $allowedValues = [
        'exception_class' => ['exception_class', 'class'],
        'exception_message' => ['exception_message', 'message'],
        'seen_at_url' => ['seen_at_url', 'url'],
    ];

    const GROUPING_AGGREGATION = '_grouping';

    public function canApply(string $pattern, array $values = []): bool
    {
        $field = $this->getFieldForValue($values['value']);

        return $field !== null;
    }

    public function pattern(): string
    {
        return '/group:(?<value>.*?)(?:$|\s)/i';
    }

    public function apply(Builder $builder, string $pattern, array $values = []): void
    {
        $field = $this->getFieldForValue($values['value']);

        $groupAggregation = new TermsAggregation(self::GROUPING_AGGREGATION, "{$field}.keyword");

        $builder->addAggregation($groupAggregation);
    }

    public function transformToHits(array $results): array
    {
        return $results['aggregations'][self::GROUPING_AGGREGATION]['buckets'];
    }

    protected function getFieldForValue($value): ?string
    {
        $allowed = array_filter(
            $this->allowedValues,
            fn(array $allowedValues, string $field) => in_array($value, $allowedValues),
            ARRAY_FILTER_USE_BOTH
        );

        return array_key_first($allowed);
    }
}

