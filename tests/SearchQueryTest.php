<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\BoolQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;
use Spatie\ElasticSearchQueryBuilder\Directives\ColumnGroupDirective;
use Spatie\ElasticSearchQueryBuilder\Directives\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Directives\FuzzyValueBaseDirective;
use Spatie\ElasticSearchQueryBuilder\SearchHit;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;
use Spatie\ElasticSearchQueryBuilder\Tests\Fakes\FakeElasticSearchClient;
use Spatie\ElasticSearchQueryBuilder\Tests\Support\PayloadFactory;

class SearchQueryTest extends TestCase
{
    /** @test */
    public function it_can_search_elastic_with_a_base_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('search query', ['title', 'content']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->search('search query');
    }

    /** @test */
    public function it_can_search_with_a_pattern_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('title:hello-world');
    }

    /** @test */
    public function it_can_search_with_multiple_pattern_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']))
            ->add(MultiMatchQuery::create('hello', ['content']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->patternDirectives(
                FuzzyKeyValuePatternDirective::forField('title', 'title'),
                FuzzyKeyValuePatternDirective::forField('content', 'content'),
            )
            ->search('title:hello-world content:hello');
    }

    /** @test */
    public function it_can_search_with_multiple_of_the_same_pattern_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']))
            ->add(MultiMatchQuery::create('hello-belgium', ['title']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->patternDirectives(
                FuzzyKeyValuePatternDirective::forField('title', 'title'),
            )
            ->search('title:hello-world title:hello-belgium');
    }

    /** @test */
    public function it_can_search_with_a_pattern_directive_with_fallback_to_the_base_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']))
            ->add(MultiMatchQuery::create('another one', ['title', 'content']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('another one title:hello-world');
    }

    /** @test */
    public function it_will_add_aggregations_for_suggestions()
    {
        $client = FakeElasticSearchClient::make()->assertAggregation(
            TermsAggregation::create('_title_suggestions', 'title.keyword'),
            TermsAggregation::create('_content_suggestions', 'content.keyword'),
        );

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('something');
    }

    /** @test */
    public function it_will_transform_hits()
    {
        $client = FakeElasticSearchClient::make()->withHits(
            PayloadFactory::hit('Hello world', 'This is a post'),
            PayloadFactory::hit('A message from Rick', 'Never gonna give you up'),
        );

        $results = SearchQuery::make($client)->search('');

        $this->assertCount(2, $results->hits);
        $this->assertEquals([
            new SearchHit(['title' => 'Hello world', 'content' => 'This is a post']),
            new SearchHit(['title' => 'A message from Rick', 'content' => 'Never gonna give you up']),
        ], $results->hits);
    }

    /** @test */
    public function it_will_append_suggestions()
    {
        $client = FakeElasticSearchClient::make()->withAggregations(
            PayloadFactory::bucketAggregation(
                '_title_suggestions',
                PayloadFactory::suggestionBucket('Hello world'),
                PayloadFactory::suggestionBucket('A message from Rick')
            )
        );

        $results = SearchQuery::make($client)
            ->patternDirectives(new FuzzyKeyValuePatternDirective('title', ['title']))
            ->search('title:test');

        $this->assertArrayHasKey('title', $results->suggestions);
        $this->assertEquals([
            'Hello world',
            'A message from Rick',
        ], $results->suggestions['title']);
    }

    /** @test */
    public function it_can_use_a_grouping_directive()
    {
        $client = FakeElasticSearchClient::make()
            ->assertSize(0)
            ->withAggregations(
                PayloadFactory::bucketAggregation(
                    '_grouping',
                    PayloadFactory::groupingBucket(['title' => 'Hello world', 'content' => 'This is a post']),
                    PayloadFactory::groupingBucket(['title' => 'A message from Rick', 'content' => 'Never gonna give you up']),
                )
            );

        $results = SearchQuery::make($client)
            ->patternDirectives(new ColumnGroupDirective(['title']))
            ->search('group:title');

        $this->assertCount(2, $results->hits);

        $this->assertEquals(
            ['title' => 'Hello world', 'content' => 'This is a post'],
            $results->hits[0]->data
        );
        $this->assertNotNull($results->hits[0]->groupingData);

        $this->assertEquals(
            ['title' => 'A message from Rick', 'content' => 'Never gonna give you up'],
            $results->hits[1]->data
        );
        $this->assertNotNull($results->hits[1]->groupingData);
    }

    /** @test */
    public function it_can_change_the_size()
    {
        $client = FakeElasticSearchClient::make()->assertSize(200);

        SearchQuery::make($client)
            ->size(200)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->search('search query');
    }

    /** @test */
    public function it_can_change_the_from()
    {
        $client = FakeElasticSearchClient::make()->assertFrom(200);

        SearchQuery::make($client)
            ->from(200)
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->search('search query');
    }

    /** @test */
    public function it_can_change_the_index()
    {
        $client = FakeElasticSearchClient::make()->assertIndex('fake-index');

        SearchQuery::make($client)
            ->index('fake-index')
            ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
            ->search('search query');
    }
}
