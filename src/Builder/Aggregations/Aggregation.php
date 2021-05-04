<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

abstract class Aggregation
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function toArray(): array;
}
