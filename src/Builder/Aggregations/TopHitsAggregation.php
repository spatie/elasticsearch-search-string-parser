<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

use Spatie\ElasticSearchQueryBuilder\Builder\Sorts\Sort;

class TopHitsAggregation extends Aggregation
{
    protected int $size;

    protected Sort $sort;

    public static function create(string $name, int $size, Sort $sort): static
    {
        return new self($name, $size, $sort);
    }

    public function __construct(
        string $name,
        int $size,
        Sort $sort
    ) {
        $this->name = $name;
        $this->size = $size;
        $this->sort = $sort;
    }

    public function toArray(): array
    {
        return [
            'top_hits' => [
                'sort' => [$this->sort->toArray()],
                'size' => $this->size,
            ],
        ];
    }
}
