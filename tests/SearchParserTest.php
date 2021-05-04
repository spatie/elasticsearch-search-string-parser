<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyValueDirective;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;

class SearchParserTest extends TestCase
{
    /** @test */
    public function it_can_create_a_search_payload_from_a_basic_text_query_with_key_value_pattern_filter()
    {
        $client = ClientBuilder::create()->build();

        $payload = SearchQuery::forClient($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'description']))
            ->filters(
                new FuzzyKeyValuePatternDirective('company', ['company_name']),
                new GroupFilter(), new HashtagGroupFilter(),
            )
            ->grouping(new FlareGrouping)
            ->query('deadly neurotoxin company:aperture group:user.id @user #group group:user.id')
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
