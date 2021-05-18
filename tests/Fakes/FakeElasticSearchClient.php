<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\Fakes;

use Elasticsearch\Client;

class FakeElasticSearchClient extends Client
{
    private array $results = [];

    public function __construct()
    {
        // We're fake
    }

    public function get(array $params = []): array
    {
        return array_pop($this->results);
    }

    public function addResult(array $result): static
    {
        array_push($this->results, $result);

        return $this;
    }
}
