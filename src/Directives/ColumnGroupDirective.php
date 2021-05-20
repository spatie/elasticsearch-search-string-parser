<?php

namespace Spatie\ElasticSearchQueryBuilder\Directives;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TopHitsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Sorts\Sort;
use Spatie\ElasticSearchQueryBuilder\SearchHit;

class ColumnGroupDirective extends GroupDirective
{
    public function __construct(protected array $groupableFields)
    {
    }

    public function canApply(string $pattern, array $values = []): bool
    {
        return in_array($values['value'], $this->groupableFields);
    }

    public function apply(Builder $builder, string $pattern, array $values = []): void
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
