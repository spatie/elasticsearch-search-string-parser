<?php

namespace Spatie\ElasticsearchStringParser\Tests\Fakes;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\Assert;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spatie\ElasticsearchQueryBuilder\AggregationCollection;
use Spatie\ElasticsearchQueryBuilder\Aggregations\Aggregation;
use Spatie\ElasticsearchQueryBuilder\Queries\Query;

/**
 * The Elasticsearch 8 client is `final`, so it can no longer be extended.
 * Instead we implement the PSR-18 HTTP client it is built on top of: we
 * assert on the outgoing request and return a canned response. The transport
 * wraps that response in an `Elasticsearch` object, just like the real client.
 */
class FakeElasticSearchClient implements ClientInterface
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
        return new self;
    }

    public function client(): Client
    {
        return ClientBuilder::create()
            ->setHttpClient($this)
            ->build();
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

    public function assertSize(?int $size): self
    {
        $this->assertions[] = 'size';
        $this->sizeAssertion = $size;

        return $this;
    }

    public function assertFrom(?int $from): self
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

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $body = json_decode((string) $request->getBody(), true) ?: [];

        parse_str($request->getUri()->getQuery(), $queryParameters);

        if (in_array('query', $this->assertions)) {
            Assert::assertEquals($this->queryAssertion->toArray(), $body['query'] ?? null);
        }

        if (in_array('aggregation', $this->assertions)) {
            Assert::assertEquals($this->aggregationAssertion->toArray(), $body['aggs'] ?? null);
        }

        if (in_array('size', $this->assertions)) {
            Assert::assertEquals($this->sizeAssertion, isset($queryParameters['size']) ? (int) $queryParameters['size'] : null);
        }

        if (in_array('from', $this->assertions)) {
            Assert::assertEquals($this->fromAssertion, isset($queryParameters['from']) ? (int) $queryParameters['from'] : null);
        }

        if (in_array('index', $this->assertions)) {
            Assert::assertEquals($this->indexAssertion, $this->indexFromPath($request->getUri()->getPath()));
        }

        return $this->toResponse([
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
        ]);
    }

    private function indexFromPath(string $path): ?string
    {
        $path = trim($path, '/');

        if ($path === '_search' || $path === '') {
            return null;
        }

        return rawurldecode(explode('/', $path)[0]);
    }

    private function toResponse(array $payload): ResponseInterface
    {
        return Psr17FactoryDiscovery::findResponseFactory()
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader(Elasticsearch::HEADER_CHECK, Elasticsearch::PRODUCT_NAME)
            ->withBody(
                Psr17FactoryDiscovery::findStreamFactory()->createStream(json_encode($payload))
            );
    }
}
