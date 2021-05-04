<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder;

use Spatie\ElasticSearchQueryBuilder\Builder\Queries\BoolQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\Query;

class Builder
{
    private ?BoolQuery $query = null;

    public function addQuery(Query $query, string $boolType = 'must'): static
    {
        if (! $this->query) {
            $this->query = new BoolQuery();
        }

        $this->query->add($query, $boolType);

        return $this;
    }

    public function addAggregate(Aggregate $aggregate): static
    {


        return $this;
    }

    public function getPayload(): array
    {
        $payload = [];

        if ($this->query) {
            $payload['query'] = $this->query->toArray();
        }

        return $payload;
    }
}
