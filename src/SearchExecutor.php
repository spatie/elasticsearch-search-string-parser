<?php

namespace Spatie\ElasticsearchStringParser;

use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchStringParser\Directives\BaseDirective;
use Spatie\ElasticsearchStringParser\Directives\GroupDirective;
use Spatie\ElasticsearchStringParser\Directives\PatternDirective;

class SearchExecutor
{
    protected array $appliedDirectives = [];

    protected ?GroupDirective $groupDirective = null;

    public function __construct(
        protected Builder $builder,
        protected array $patternDirectives = [],
        protected ?BaseDirective $baseDirective = null,
    ) {
    }

    public function __invoke(string $query): SearchResults
    {
        $this->applyQueryToBuilder($query);

        if ($this->groupDirective) {
            $this->builder->size(0);
            $this->builder->from(0);
        }

        $results = $this->builder->search();

        $hits = $this->groupDirective
            ? $this->groupDirective->transformToHits($results)
            : array_map(
                fn (array $hit) => new SearchHit($hit['_source']),
                $results['hits']['hits']
            );

        $suggestions = collect($this->appliedDirectives)
            ->mapWithKeys(fn (BaseDirective | PatternDirective $directive) => [
                $directive->getKey() => $directive->transformToSuggestions($results),
            ])
            ->toArray();

        return new SearchResults(
            $hits,
            $suggestions,
            $this->groupDirective !== null,
            $results
        );
    }

    protected function applyQueryToBuilder(string $query): void
    {
        $queryWithoutDirectives = array_reduce(
            $this->patternDirectives,
            fn (string $query, PatternDirective $directive) => $this->applyDirective($directive, $query),
            $query
        );

        $queryWithoutDirectives = trim($queryWithoutDirectives);

        if ($this->baseDirective && $this->baseDirective->canApply($queryWithoutDirectives)) {
            $this->baseDirective->apply($this->builder, $queryWithoutDirectives);

            $this->appliedDirectives[] = $this->baseDirective;
        }
    }

    protected function applyDirective(PatternDirective $directive, string $query): string
    {
        $matchCount = preg_match_all($directive->pattern(), $query, $matches, PREG_SET_ORDER);

        if (! $matchCount) {
            return $query;
        }

        $matches = array_filter(
            $matches,
            fn (array $match) => $directive->canApply(array_shift($match), $match)
        );

        if (empty($matches)) {
            return $query;
        }

        foreach ($matches as $match) {
            if ($directive instanceof GroupDirective) {
                if ($this->groupDirective) {
                    continue;
                } else {
                    $this->groupDirective = $directive;
                }
            }

            $directive->apply($this->builder, array_shift($match), $match);

            $this->appliedDirectives[] = $directive;
        }

        return preg_filter($directive->pattern(), '', $query);
    }
}
