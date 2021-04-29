<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyFieldsValueFilter;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;

class SearchParserTest extends TestCase
{
    /** @test */
    public function it_can_create_a_search_payload()
    {
        $client = ClientBuilder::create()->build();

        $payload = SearchQuery::forClient($client)
            ->defaultFilter(new FuzzyFieldsValueFilter(['title', 'description']))
            ->query('deadly neurotoxin')
            ->getBuilder()
            ->getPayload();

        $this->assertEquals([
            'bool' => [
                'must' => [
                    [
                        'multi_match' => [
                            'query' => 'deadly neurotoxin',
                            'fields' => [
                                'title',
                                'description',
                            ],
                            'fuzziness' => 2,
                        ],
                    ],
                ],
            ],
        ], $payload['query']);
    }
}
