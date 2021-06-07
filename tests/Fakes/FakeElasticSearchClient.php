<?php

namespace Spatie\ElasticsearchStringParser\Tests\Fakes;

use Elasticsearch\Client;
use PHPUnit\Framework\Assert;
use Spatie\ElasticsearchQueryBuilder\AggregationCollection;
use Spatie\ElasticsearchQueryBuilder\Aggregations\Aggregation;
use Spatie\ElasticsearchQueryBuilder\Queries\Query;

class FakeElasticSearchClient extends Client
{
    private ?Query $queryAssertion = null;

    private ?AggregationCollection $aggregationAssertion = null;

    private ?int $sizeAssertion = null;

    private ?string $indexAssertion = null;

    private ?int $fromAssertion = null;

    private array $hits = [];

    private array $aggregations = [];

    private array $assertions = [];

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
        $this->assertions[] = 'query';
        $this->queryAssertion = $query;

        return $this;
    }

    public function assertAggregation(Aggregation ...$aggregations): self
    {
        $this->assertions[] = 'aggregation';
        $this->aggregationAssertion = new AggregationCollection(...$aggregations);

        return $this;
    }

    public function assertIndex(?string $index): self
    {
        $this->assertions[] = 'index';
        $this->indexAssertion = $index;

        return $this;
    }

    public function assertSize(?int $size)
    {
        $this->assertions[] = 'size';
        $this->sizeAssertion = $size;

        return $this;
    }

    public function assertFrom(?int $from)
    {
        $this->assertions[] = 'from';
        $this->fromAssertion = $from;

        return $this;
    }

    public function withHits(array ...$hits): self
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
        if (in_array('query', $this->assertions)) {
            Assert::assertEquals($this->queryAssertion->toArray(), $params['body']['query']);
        }

        if(in_array('aggregation', $this->assertions)){
            Assert::assertEquals($this->aggregationAssertion->toArray(), $params['body']['aggs']);
        }

        if(in_array('size', $this->assertions)){
            Assert::assertEquals($this->sizeAssertion, $params['size'] ?? null);
        }

        if(in_array('from', $this->assertions)){
            Assert::assertEquals($this->fromAssertion, $params['from'] ?? null);
        }

        if(in_array('index', $this->assertions)){
            Assert::assertEquals($this->indexAssertion, $params['index'] ?? null);
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
