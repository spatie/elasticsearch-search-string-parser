<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use Spatie\ElasticsearchQueryBuilder\Aggregations\TermsAggregation;
use Spatie\ElasticsearchQueryBuilder\Aggregations\TopHitsAggregation;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchStringParser\SearchHit;

class ColumnGroupDirective extends GroupDirective
{
    public function __construct(protected array $groupableFields)
    {
    }

    public function canApply(string $pattern, array $values = []): bool
    {
        return in_array($values['value'], $this->groupableFields);
    }

    public function apply(Builder $builder, string $pattern, array $values, int $patternOffsetStart, int $patternOffsetEnd): void
    {
        $field = $values[0];

        $aggregation = TermsAggregation::create('_grouping', "{$field}.keyword")
            ->aggregation(TopHitsAggregation::create('top_hit', 1));

        $builder->addAggregation($aggregation);
    }

    public function pattern(): string
    {
        return '/group:(?<value>.*?)(?:$|\s)/i';
    }

    public function transformToHits(array $results): array
    {
        return array_map(
            fn(array $bucket) => new SearchHit(
                $bucket['top_hit']['hits']['hits'][0]['_source'],
                $bucket
            ),
            $results['aggregations']['_grouping']['buckets']
        );
    }
}
