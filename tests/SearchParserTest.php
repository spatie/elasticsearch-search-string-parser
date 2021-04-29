<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternFilter;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyValueFilter;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;

class SearchParserTest extends TestCase
{
    /** @test */
    public function it_can_create_a_search_payload()
    {
        $client = ClientBuilder::create()->build();

        $payload = SearchQuery::forClient($client)
            ->defaultFilter(new FuzzyValueFilter(['title', 'description']))
            ->patternFilters(
                new FuzzyKeyValuePatternFilter('company', ['company_name'])
            )
            ->query('deadly neurotoxin company:aperture')
            ->getBuilder()
            ->getPayload();

        $this->assertEquals([
            'bool' => [
                'must' => [
                    [
                        'multi_match' => [
                            'query' => 'aperture',
                            'fields' => [
                                'company_name',
                            ],
                            'fuzziness' => 2,
                        ],
                    ],
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
