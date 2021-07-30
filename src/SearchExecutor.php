<?php

namespace Spatie\ElasticsearchStringParser;

use Closure;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchStringParser\Directives\BaseDirective;
use Spatie\ElasticsearchStringParser\Directives\GroupDirective;
use Spatie\ElasticsearchStringParser\Directives\PatternDirective;
use Spatie\ElasticsearchStringParser\Support\Regex;

class SearchExecutor
{
    protected array $appliedDirectives = [];

    protected ?GroupDirective $groupDirective = null;

    public function __construct(
        protected Builder $builder,
        protected array $patternDirectives = [],
        protected ?BaseDirective $baseDirective = null,
        protected ?Closure $beforeApplying = null,
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
            ->mapWithKeys(fn (BaseDirective | PatternDirective $directive, string $matchedPattern) => [
                $matchedPattern => $directive->transformToSuggestions($results),
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
        foreach ($this->patternDirectives as $directive) {
            $this->applyDirective($directive, $query);
        }

        $patterns = array_map(
            fn (PatternDirective $directive) => $directive->pattern(),
            $this->patternDirectives
        );

        $queryWithoutDirectives = trim(preg_replace($patterns, '', $query));

        if ($this->baseDirective && $this->baseDirective->canApply($queryWithoutDirectives)) {
            $this->baseDirective->apply($this->builder, $queryWithoutDirectives);

            $this->appliedDirectives[$queryWithoutDirectives] = $this->baseDirective;
        }
    }

    protected function applyDirective(PatternDirective $directive, string $query): void
    {
        $matchCount = Regex::mb_preg_match_all($directive->pattern(), $query, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (! $matchCount) {
            return;
        }

        collect($matches)
            ->map(fn (array $match) => array_merge(
                array_map(fn ($matchGroup) => $matchGroup[0], $match),
                [
                    'pattern_offset_start' => $match[0][1],
                    'pattern_offset_end' => $match[0][1] + mb_strlen($match[0][0]),
                ]
            ))
            ->filter(fn (array $match) => $directive->canApply(array_shift($match), $match))
            ->each(function (array $match) use ($directive) {
                if ($directive instanceof GroupDirective) {
                    if ($this->groupDirective) {
                        return;
                    } else {
                        $this->groupDirective = $directive;
                    }
                }
                $directiveForMatch = clone $directive;
                $fullMatch = array_shift($match);
                $offsetEnd = array_pop($match);
                $offsetStart = array_pop($match);

                if ($this->beforeApplying) {
                    ($this->beforeApplying)($directiveForMatch, $fullMatch, $match, $offsetStart, $offsetEnd);
                }

                $directiveForMatch->apply($this->builder, $fullMatch, $match, $offsetStart, $offsetEnd);

                $this->appliedDirectives[$fullMatch] = $directiveForMatch;
            });
    }
}
