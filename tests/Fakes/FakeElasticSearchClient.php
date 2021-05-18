<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\Fakes;

use Elasticsearch\Client;
use PHPUnit\Framework\Assert;
use Spatie\ElasticSearchQueryBuilder\Builder\AggregationCollection;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Aggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\Query;

class FakeElasticSearchClient extends Client
{
    private ?Query $expectedQuery = null;

    private ?AggregationCollection $expectedAggregations = null;

    private array $hits = [];

    private array $aggregations = [];

    public static function make(): static
    {
        return new self();
    }

    public function __construct()
    {
        // We're fake
    }

    public function assertQuery(Query $query): self
    {
        $this->expectedQuery = $query;

        return $this;
    }

    public function expectAggregations(Aggregation ...$aggregations): self
    {
        $this->expectedAggregations = new AggregationCollection(...$aggregations);

        return $this;
    }

    public function withHits(array $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    public function withAggregations(array $aggregations): self
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    public function search(array $params = [])
    {
        if ($this->expectedQuery) {
            Assert::assertEquals($this->expectedQuery->toArray(), $params['body']['query']);
        }

        if($this->expectedAggregations){
            Assert::assertEquals($this->expectedAggregations->toArray(), $params['body']['aggs']);
        }

        return [
            'took' => 42,
            'timed_out' => false,
            'hits' => [
                'hits' => $this->hits,
                'max_score' => 1.5,
                'total' => [
                    'value' => count($this->hits),
                    'relations' => 'eq',
                ],
            ],
            'aggregations' => $this->aggregations,
        ];
    }
}
