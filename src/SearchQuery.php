<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\Directive;
use Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective;

class SearchQuery
{
    /** @var \Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective[] */
    protected array $directives = [];

    protected ?Directive $baseDirective = null;

    protected Builder $builder;

    protected Client $client;

    public function __construct(
        Client $client,
        ?Builder $builder = null
    ) {
        $this->client = $client;
        $this->builder = $builder ?? new Builder();
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

    public function directives(PatternDirective ...$filters): static
    {
        $this->directives = $filters;

        return $this;
    }

    public function search(string $query): ResultsCollection
    {
        $this->applyQuery($query);

        $payload = $this->builder->getPayload();

        $results = $this->client->get($payload);

        return new ResultsCollection($results);
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    protected function applyQuery(string $query): void
    {
        $queryWithoutDirectives = collect($this->directives)
            ->reduce(function (string $query, PatternDirective $filter) {
                $matchCount = preg_match_all($filter->pattern(), $query, $matches, PREG_SET_ORDER);

                if (!$matchCount) {
                    return $query;
                }

                collect($matches)
                    ->each(fn(array $match) => $filter->apply($this->builder, array_shift($match), $match));

                return preg_filter($filter->pattern(), '', $query);
            }, $query);

        $queryWithoutDirectives = trim($queryWithoutDirectives);

        if ($this->baseDirective) {
            $this->baseDirective->apply($this->builder, $queryWithoutDirectives);
        }
    }
}
