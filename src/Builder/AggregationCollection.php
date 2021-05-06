<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Aggregation;

class AggregationCollection
{
    private array $aggregations;

    public function __construct(Aggregation ...$aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function add(Aggregation $aggregation): self
    {
        $this->aggregations[] = $aggregation;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->aggregations);
    }

    public function toArray(): array
    {
        $aggregations = [];

        foreach($this->aggregations as $aggregation) {
            $aggregations[$aggregation->getName()] = $aggregation->toArray();
        }

        return $aggregations;
    }
}
