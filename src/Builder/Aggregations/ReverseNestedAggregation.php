<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

use Spatie\ElasticSearchQueryBuilder\Builder\AggregationCollection;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Concerns\WithAggregations;
use stdClass;

class ReverseNestedAggregation extends Aggregation
{
    use WithAggregations;

    public static function create(
        string $name,
        Aggregation ...$aggregations
    ): self {
        return new self($name, ...$aggregations);
    }

    public function __construct(
        string $name,
        Aggregation ...$aggregations
    ) {
        $this->name = $name;
        $this->aggregations = new AggregationCollection(...$aggregations);
    }

    public function toArray(): array
    {
        return [
            'reverse_nested' => new stdClass(),
            'aggs' => $this->aggregations->toArray(),
        ];
    }
}
