<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;

class SearchParserTest extends TestCase
{
    /** @test */
    public function it_can_create_a_search_payload()
    {
        $client = ClientBuilder::create()->build();

        $payload = SearchQuery::forClient($client)
            ->fields(['title', 'description'])
            ->query('deadly neurotoxin')
            ->getBuilder()
            ->getPayload();

        $this->assertEquals([
            'multi_match' => [
                'query' => 'deadly neurotoxin',
                'fields' => [
                    'title',
                    'description',
                ],
            ],
        ], $payload['query']);
    }
}
