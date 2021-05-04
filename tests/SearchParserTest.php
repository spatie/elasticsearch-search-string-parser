<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyValueDirective;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;
use Spatie\ElasticSearchQueryBuilder\Tests\stubs\FlareGroupDirective;
use Spatie\Snapshots\MatchesSnapshots;

class SearchParserTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_create_a_search_payload_from_a_basic_text_query_with_key_value_pattern_filter()
    {
        $client = ClientBuilder::create()->build();

        $searchQuery = SearchQuery::make($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'description']))
            ->directives(
                new FuzzyKeyValuePatternDirective('company', ['company_name']),
                new FlareGroupDirective()
            );

        $searchQuery->search('deadly neurotoxin company:aperture group:url');

        $payload = $searchQuery->getBuilder()->getPayload();

        $this->assertMatchesJsonSnapshot($payload);
    }
}
