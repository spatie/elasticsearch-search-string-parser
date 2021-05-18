<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests;

use Spatie\ElasticSearchQueryBuilder\SearchQuery;
use Spatie\ElasticSearchQueryBuilder\Tests\Fakes\FakeElasticSearchClient;

class SearchQueryTest extends TestCase
{
    /** @test */
    public function it_can_search_elastic()
    {
        $client = new FakeElasticSearchClient();

        $query = SearchQuery::make($client);

        return $query
            ->baseDirective()
            ->search('hello')
    }
}
