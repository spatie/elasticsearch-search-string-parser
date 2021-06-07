<?php

namespace Spatie\ElasticsearchStringParser\Tests\stubs;

use Spatie\ElasticsearchQueryBuilder\Aggregations\CardinalityAggregation;
use Spatie\ElasticsearchQueryBuilder\Aggregations\MaxAggregation;
use Spatie\ElasticsearchQueryBuilder\Aggregations\MinAggregation;
use Spatie\ElasticsearchQueryBuilder\Aggregations\TermsAggregation;
use Spatie\ElasticsearchQueryBuilder\Aggregations\TopHitsAggregation;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchQueryBuilder\Sorts\Sort;
use Spatie\ElasticsearchStringParser\Directives\GroupDirective;
use Spatie\ElasticsearchStringParser\SearchHit;
use Spatie\ElasticsearchStringParser\Tests\stubs\Data\ErrorOccurrence;
use Spatie\ElasticsearchStringParser\Tests\stubs\Data\ErrorOccurrenceGrouping;
use Spatie\ElasticsearchStringParser\Tests\stubs\Data\ErrorOccurrenceHit;

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

        $termsAggregation = TermsAggregation::create(self::GROUPING_AGGREGATION, "{$field}.keyword")
            ->missing(0)
            ->order([
                'last_received_at' => 'desc',
            ])
            ->aggregation(MaxAggregation::create('last_received_at', 'received_at'))
            ->aggregation(MinAggregation::create('first_received_at', 'received_at'))
            ->aggregation(TopHitsAggregation::create('recent_error_occurrence', 1, Sort::create('received_at', Sort::DESC)));

        $countAggregation = CardinalityAggregation::create('distinct_count', "{$field}.keyword")
            ->missing(0);

        $builder
            ->addAggregation($termsAggregation)
            ->addAggregation($countAggregation);
    }

    public function transformToHits(array $results): array
    {
        return array_map(
            fn(array $bucket) => new SearchHit(
                $bucket['recent_error_occurrence']['hits']['hits'][0]['_source'],
                $bucket,
            ),
            $results['aggregations'][self::GROUPING_AGGREGATION]['buckets']
        );
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

