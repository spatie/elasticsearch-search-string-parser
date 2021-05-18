<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\BoolQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyValueDirective;
use Spatie\ElasticSearchQueryBuilder\SearchQuery;
use Spatie\ElasticSearchQueryBuilder\Tests\Fakes\FakeElasticSearchClient;

class SearchQueryTest extends TestCase
{
    /** @test */
    public function it_can_search_elastic_with_a_base_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('search query', ['title', 'content']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'content']))
            ->search('search query');
    }

    /** @test */
    public function it_can_search_with_a_pattern_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'content']))
            ->directives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('title:hello-world');
    }

    /** @test */
    public function it_can_search_with_a_pattern_directive_with_fallback_to_the_base_directive()
    {
        $expectedQuery = BoolQuery::create()
            ->add(MultiMatchQuery::create('hello-world', ['title']))
            ->add(MultiMatchQuery::create('another one', ['title', 'content']));

        $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'content']))
            ->directives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('another one title:hello-world');
    }

    /** @test */
    public function it_will_add_aggregations_for_suggestions()
    {
        $client = FakeElasticSearchClient::make()->expectAggregations(
            TermsAggregation::create('_title_suggestions', 'title.keyword'),
            TermsAggregation::create('_content_suggestions', 'content.keyword'),
        );

        SearchQuery::make($client)
            ->baseDirective(new FuzzyValueDirective(['title', 'content']))
            ->directives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
            ->search('something');
    }
}
