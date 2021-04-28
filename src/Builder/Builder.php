<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\BoolQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\Query;

class Builder
{
    private ?BoolQuery $query;

    public function addQuery(Query $query, string $boolType = 'must'): static
    {
        if (! $this->query) {
            $this->query = new BoolQuery();
        }

        $this->query->add($query, $boolType);

        return $this;
    }
}
