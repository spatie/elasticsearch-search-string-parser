<?php

use Spatie\ElasticsearchQueryBuilder\Aggregations\TermsAggregation;
use Spatie\ElasticsearchQueryBuilder\Queries\BoolQuery;
use Spatie\ElasticsearchQueryBuilder\Queries\MultiMatchQuery;
use Spatie\ElasticsearchStringParser\Directives\ColumnGroupDirective;
use Spatie\ElasticsearchStringParser\Directives\FuzzyKeyValuePatternDirective;
use Spatie\ElasticsearchStringParser\Directives\FuzzyValueBaseDirective;
use Spatie\ElasticsearchStringParser\Directives\PatternDirective;
use Spatie\ElasticsearchStringParser\SearchHit;
use Spatie\ElasticsearchStringParser\SearchQuery;
use Spatie\ElasticsearchStringParser\Suggestion;
use Spatie\ElasticsearchStringParser\Tests\Fakes\FakeElasticSearchClient;
use Spatie\ElasticsearchStringParser\Tests\Support\PayloadFactory;

it('can search elastic with a base directive', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('search query', ['title', 'content'], fuzziness: 'auto'));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->search('search query');
});

it('can search with a pattern directive', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('hello-world', ['title'], fuzziness: 'auto'));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
        ->search('title:hello-world');
});

it('can search with multiple pattern directives', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('hello-world', ['title'], fuzziness: 'auto'))
        ->add(MultiMatchQuery::create('hello', ['content'], fuzziness: 'auto'));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->patternDirectives(
            FuzzyKeyValuePatternDirective::forField('title', 'title'),
            FuzzyKeyValuePatternDirective::forField('content', 'content'),
        )
        ->search('title:hello-world content:hello');
});

it('can search with multiple of the same pattern directive', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('hello-world', ['title'], fuzziness: 'auto'))
        ->add(MultiMatchQuery::create('hello-belgium', ['title'], fuzziness: 'auto'));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->patternDirectives(
            FuzzyKeyValuePatternDirective::forField('title', 'title'),
        )
        ->search('title:hello-world title:hello-belgium');
});

it('can modify the directive instance before being applied', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('hello-world', ['title'], fuzziness: 'auto'))
        ->add(MultiMatchQuery::create('hello-belgium', ['title'], fuzziness: 100));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->patternDirectives(
            FuzzyKeyValuePatternDirective::forField('title', 'title'),
        )
        ->beforeApplying(function (PatternDirective $directive, string $match) {
            if ($match === 'title:hello-belgium' && $directive instanceof FuzzyKeyValuePatternDirective) {
                $directive->setFuzziness(100);
            }
        })
        ->search('title:hello-world title:hello-belgium');
});

it('knows at what offset the directive was matched', function () {
    $client = FakeElasticSearchClient::make();

    $matches = [];

    SearchQuery::forClient($client->client())
        ->patternDirectives(
            FuzzyKeyValuePatternDirective::forField('title', 'title'),
            FuzzyKeyValuePatternDirective::forField('content', 'content'),
        )
        ->beforeApplying(function (PatternDirective $directive, string $match, array $_values, int $startOffset, int $endOffset) use (&$matches) {
            $matches[$match] = [$startOffset, $endOffset];
        })
        ->search('title:hello-world content:hello title:hello-belgium');

    expect($matches)->toEqual([
        'title:hello-world ' => [0, 18],
        'content:hello ' => [18, 32],
        'title:hello-belgium' => [32, 51],
    ]);
});

it('can search with a pattern directive with fallback to the base directive', function () {
    $expectedQuery = BoolQuery::create()
        ->add(MultiMatchQuery::create('hello-world', ['title'], fuzziness: 'auto'))
        ->add(MultiMatchQuery::create('another one', ['title', 'content'], fuzziness: 'auto'));

    $client = FakeElasticSearchClient::make()->assertQuery($expectedQuery);

    SearchQuery::forClient($client->client())
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title'))
        ->search('another one title:hello-world');
});

it('will add aggregations for suggestions', function () {
    $client = FakeElasticSearchClient::make()->assertAggregation(
        TermsAggregation::create('_title_suggestions', 'title.keyword'),
        TermsAggregation::create('_content_suggestions', 'content.keyword'),
    );

    SearchQuery::forClient($client->client())
        ->baseDirective((new FuzzyValueBaseDirective(['title', 'content']))->withSuggestions())
        ->patternDirectives(FuzzyKeyValuePatternDirective::forField('title', 'title')->withSuggestions())
        ->search('something');
});

it('will transform hits', function () {
    $client = FakeElasticSearchClient::make()->withHits(
        PayloadFactory::hit('Hello world', 'This is a post'),
        PayloadFactory::hit('A message from Rick', 'Never gonna give you up'),
    );

    $results = SearchQuery::forClient($client->client())->search('');

    expect($results->hits)
        ->toHaveCount(2)
        ->toEqual([
            new SearchHit(['title' => 'Hello world', 'content' => 'This is a post']),
            new SearchHit(['title' => 'A message from Rick', 'content' => 'Never gonna give you up']),
        ]);
});

it('will append suggestions', function () {
    $client = FakeElasticSearchClient::make()->withAggregations(
        PayloadFactory::bucketAggregation(
            '_title_suggestions',
            PayloadFactory::suggestionBucket('Hello world'),
            PayloadFactory::suggestionBucket('A message from Rick')
        )
    );

    $results = SearchQuery::forClient($client->client())
        ->patternDirectives((new FuzzyKeyValuePatternDirective('title', ['title']))->withSuggestions())
        ->search('title:test');

    expect($results->suggestions)->toHaveKey('title:test');
    expect($results->suggestions['title:test'])->toEqual([
        new Suggestion('Hello world'),
        new Suggestion('A message from Rick'),
    ]);
});

it('can use a grouping directive', function () {
    $client = FakeElasticSearchClient::make()
        ->assertSize(0)
        ->withAggregations(
            PayloadFactory::bucketAggregation(
                '_grouping',
                PayloadFactory::groupingBucket(['title' => 'Hello world', 'content' => 'This is a post']),
                PayloadFactory::groupingBucket(['title' => 'A message from Rick', 'content' => 'Never gonna give you up']),
            )
        );

    $results = SearchQuery::forClient($client->client())
        ->patternDirectives(new ColumnGroupDirective(['title']))
        ->search('group:title');

    expect($results->hits)->toHaveCount(2);

    expect($results->hits[0]->data)->toEqual(['title' => 'Hello world', 'content' => 'This is a post']);
    expect($results->hits[0]->groupingData)->not->toBeNull();

    expect($results->hits[1]->data)->toEqual(['title' => 'A message from Rick', 'content' => 'Never gonna give you up']);
    expect($results->hits[1]->groupingData)->not->toBeNull();
});

it('can change the size', function () {
    $client = FakeElasticSearchClient::make()->assertSize(200);

    SearchQuery::forClient($client->client())
        ->size(200)
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->search('search query');
});

it('can change the from', function () {
    $client = FakeElasticSearchClient::make()->assertFrom(200);

    SearchQuery::forClient($client->client())
        ->from(200)
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->search('search query');
});

it('can change the index', function () {
    $client = FakeElasticSearchClient::make()->assertIndex('fake-index');

    SearchQuery::forClient($client->client())
        ->index('fake-index')
        ->baseDirective(new FuzzyValueBaseDirective(['title', 'content']))
        ->search('search query');
});
