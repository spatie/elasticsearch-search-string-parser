<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\Directive;
use Spatie\ElasticSearchQueryBuilder\Filters\FuzzyKeyValuePatternDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\GroupDirective;
use Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective;

class SearchQuery
{
    /** @var \Spatie\ElasticSearchQueryBuilder\Filters\PatternDirective[] */
    protected array $patternDirectives = [];

    protected ?Directive $baseDirective = null;

    protected Builder $builder;

    protected Client $client;

    protected ?string $searchIndex = null;

    protected ?GroupDirective $groupDirective = null;

    protected ?int $size = null;

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

    public function index(string $searchIndex): static
    {
        $this->searchIndex = $searchIndex;

        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;

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
        $appliedDirectives = $this->applyQuery($query);

        $payload = $this->builder->getPayload();

        $params = [
            'body' => $payload,
        ];

        if ($this->searchIndex) {
            $params['index'] = $this->searchIndex;
        }

        if ($this->groupDirective) {
            $params['size'] = 0;
        }

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        $results = $this->client->search($params);

        $hits = $this->groupDirective
            ? $this->groupDirective->transformToHits($results)
            : array_map(
                fn(array $hit) => new SearchHit($hit['_source']),
                $results['hits']['hits']
            );

        $suggestions = collect($appliedDirectives)
            ->mapWithKeys(function (Directive|PatternDirective $directive) use ($results) {
                $name = $directive instanceof FuzzyKeyValuePatternDirective
                    ? $directive->getKey()
                    : $directive::class;

                $suggestions = $directive->transformToSuggestions($results);

                return [$name => $suggestions];
            })
            ->toArray();

        return new SearchResults($hits, $suggestions, $results);
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    protected function applyQuery(string $query): array
    {
        $appliedDirectives = [];

        $queryWithoutDirectives = collect($this->patternDirectives)
            ->reduce(function (string $query, PatternDirective $directive) use (&$appliedDirectives) {
                $matchCount = preg_match_all($directive->pattern(), $query, $matches, PREG_SET_ORDER);

                if (! $matchCount) {
                    return $query;
                }

                collect($matches)
                    ->filter(fn(array $match) => $directive->canApply(array_shift($match), $match))
                    ->each(function (array $match) use (&$appliedDirectives, $directive) {
                        if ($directive instanceof GroupDirective) {
                            if ($this->groupDirective) {
                                return;
                            } else {
                                $this->groupDirective = $directive;
                            }
                        }

                        $directive->apply($this->builder, array_shift($match), $match);

                        $appliedDirectives[] = $directive;
                    });

                return preg_filter($directive->pattern(), '', $query);
            }, $query);

        $queryWithoutDirectives = trim($queryWithoutDirectives);

        if ($this->baseDirective && $this->baseDirective->canApply($queryWithoutDirectives)) {
            $this->baseDirective->apply($this->builder, $queryWithoutDirectives);

            $appliedDirectives[] = $this->baseDirective;
        }

        return $appliedDirectives;
    }
}
