<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Concerns;

use Spatie\ElasticSearchQueryBuilder\Builder\AggregationCollection;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Aggregation;

trait WithAggregations
{
    private AggregationCollection $aggregations;

    public function aggregation(Aggregation $aggregation): self
    {
        $this->aggregations->add($aggregation);

        return $this;
    }
}
