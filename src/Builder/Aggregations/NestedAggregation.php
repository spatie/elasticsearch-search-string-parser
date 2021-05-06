<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

use Spatie\ElasticSearchQueryBuilder\Builder\AggregationCollection;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Concerns\WithAggregations;

class NestedAggregation extends Aggregation
{
    use WithAggregations;

    private string $path;

    public static function create(
        string $name,
        string $path,
        Aggregation ...$aggregations
    ): self {
        return new self($name, $path, ...$aggregations);
    }

    public function __construct(
        string $name,
        string $path,
        Aggregation ...$aggregations
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->aggregations = new AggregationCollection(...$aggregations);
    }

    public function toArray(): array
    {
        return [
            'nested' => [
                'path' => $this->path,
            ],
            'aggs' => $this->aggregations->toArray(),
        ];
    }
}
