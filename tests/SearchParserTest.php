<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyValueDirective;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;
use Spatie\ElasticSearchQueryBuilder\Tests\stubs\Data\ErrorOccurrence;
use Spatie\ElasticSearchQueryBuilder\Tests\stubs\Data\ErrorOccurrenceHit;
use Spatie\ElasticSearchQueryBuilder\Tests\stubs\FlareContextGroupDirective;
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

    /** @test */
    public function it_searches()
    {
        $client = ClientBuilder::create()->build();

        $searchQuery = SearchQuery::make($client)
            ->index('error_occurrences')
            ->hitTransformer(fn(array $hit) => new ErrorOccurrenceHit(
                ErrorOccurrence::fromPayload($hit['_source'])
            ))
            ->baseDirective(new FuzzyValueDirective(['exception_message', 'exception_class']))
            ->directives(
                new FuzzyKeyValuePatternDirective('company', ['exception_class']),
                new FlareGroupDirective()
            );

        dd($searchQuery->search('InvalidArgumentException'));
    }


    /** @test */
    public function it_groups()
    {
        $client = ClientBuilder::create()->build();

        $searchQuery = SearchQuery::make($client)
            ->index('error_occurrences')
            ->size(0)
            ->baseDirective(new FuzzyValueDirective(['exception_message', 'exception_class']))
            ->directives(
                new FuzzyKeyValuePatternDirective('company', ['exception_class']),
                new FlareGroupDirective()
            );

        dd($searchQuery->search('group:exception_class'));
    }

    /** @test */
    public function it_groups_context()
    {
        $client = ClientBuilder::create()->build();

        $searchQuery = SearchQuery::make($client)
            ->index('error_occurrences')
            ->size(0)
            ->baseDirective(new FuzzyValueDirective(['exception_message', 'exception_class']))
            ->directives(
                new FuzzyKeyValuePatternDirective('company', ['exception_class']),
                new FlareContextGroupDirective()
            );

        dd($searchQuery->search('group:useragent'));
    }
}
