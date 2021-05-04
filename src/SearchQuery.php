<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\Directive;
use Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective;

class SearchQuery
{
    /** @var \Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective[] */
    protected array $patternDirectives = [];

    protected ?Directive $baseDirective = null;

    protected Builder $builder;

    protected Client $client;

    protected ?string $searchIndex = null;

    public function __construct(
        Client $client,
        ?Builder $builder = null
    ) {
        $this->client = $client;
        $this->builder = $builder ?? new Builder();
    }

    public static function make(
        Client $client,
        ?Builder $builder = null
    ): static {
        return new static($client, $builder);
    }

    public function setElasticsearchIndex(string $searchIndex): static
    {
        $this->searchIndex = $searchIndex;

        return $this;
    }

    /**
     * This directive will be applied to the remainder of the search query
     * after all other directive have been applied and removed from the
     * search string.
     *
     * @param \Spatie\ElasticSearchQueryBuilder\Filters\Directive $filter
     *
     * @return $this
     */
    public function baseDirective(Directive $filter): static
    {
        $this->baseDirective = $filter;

        return $this;
    }

    public function directives(PatternDirective ...$patternDirectives): static
    {
        $this->patternDirectives = $patternDirectives;

        return $this;
    }

    public function search(string $query): SearchResults
    {
        $this->applyQuery($query);

        $payload = $this->builder->getPayload();

        $params = [
            'body' => $payload,
        ];

        if ($this->searchIndex) {
            $params['index'] = $this->searchIndex;
        }

        $results = $this->client->search($params);

        if ($groupDirective = $this->getGroupDirective()) {
            $hits = $results['aggregations']['???']['buckets'];
        } else {
            $hits = $results['hits']['hits'];
        }

    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    protected function applyQuery(string $query): void
    {
        $queryWithoutDirectives = collect($this->patternDirectives)
            ->reduce(function (string $query, PatternDirective $filter) {
                $matchCount = preg_match_all($filter->pattern(), $query, $matches, PREG_SET_ORDER);

                if (!$matchCount) {
                    return $query;
                }

                collect($matches)
                    ->filter(fn(array $match) => $filter->canApply(array_shift($match), $match))
                    ->each(fn(array $match) => $filter->apply($this->builder, array_shift($match), $match));

                return preg_filter($filter->pattern(), '', $query);
            }, $query);

        $queryWithoutDirectives = trim($queryWithoutDirectives);

        if ($this->baseDirective && $this->baseDirective->canApply($queryWithoutDirectives)) {
            $this->baseDirective->apply($this->builder, $queryWithoutDirectives);
        }
    }
}
