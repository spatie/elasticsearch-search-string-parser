<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Aggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\BoolQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\Query;

class Builder
{
    protected ?BoolQuery $query = null;

    protected ?AggregationCollection $aggregations = null;

    public function addQuery(Query $query, string $boolType = 'must'): static
    {
        if (! $this->query) {
            $this->query = new BoolQuery();
        }

        $this->query->add($query, $boolType);

        return $this;
    }

    public function addAggregation(Aggregation $aggregation): static
    {
        if (! $this->aggregations) {
            $this->aggregations = new AggregationCollection();
        }

        $this->aggregations->add($aggregation);

        return $this;
    }

    public function getPayload(): array
    {
        $payload = [];

        if ($this->query) {
            $payload['query'] = $this->query->toArray();
        }

        if ($this->aggregations) {
            $payload['aggs'] = $this->aggregations->toArray();
        }

        return $payload;
    }
}
