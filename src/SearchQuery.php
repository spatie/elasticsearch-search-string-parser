<?php

namespace Spatie\ElasticsearchStringParser;

use Elasticsearch\Client;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchStringParser\Concerns\ForwardsCalls;
use Spatie\ElasticsearchStringParser\Directives\BaseDirective;
use Spatie\ElasticsearchStringParser\Directives\GroupDirective;
use Spatie\ElasticsearchStringParser\Directives\PatternDirective;

/** @mixin \Spatie\ElasticsearchQueryBuilder\Builder */
class SearchQuery
{
    use ForwardsCalls;

    /** @var \Spatie\ElasticsearchStringParser\Directives\PatternDirective[] */
    protected array $patternDirectives = [];

    protected ?BaseDirective $baseDirective = null;

    protected Builder $builder;

    protected ?GroupDirective $groupDirective = null;

    public function __construct(
        Builder $builder
    ) {
        $this->builder = $builder;
    }

    public static function forClient(
        Client $client
    ): static {
        return new static(new Builder($client));
    }

    /**
     * This directive will be applied to the remainder of the search query
     * after all other directive have been applied and removed from the
     * search string.
     *
     * @param \Spatie\ElasticsearchStringParser\Directives\BaseDirective $filter
     *
     * @return $this
     */
    public function baseDirective(BaseDirective $filter): static
    {
        $this->baseDirective = $filter;

        return $this;
    }

    public function patternDirectives(PatternDirective ...$patternDirectives): static
    {
        $this->patternDirectives = $patternDirectives;

        return $this;
    }

    public function search(string $query): SearchResults
    {
        $searchExecutor = new SearchExecutor(
            clone $this->builder,
            $this->patternDirectives,
            $this->baseDirective,
        );

        return $searchExecutor($query);
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    public function __call(string $method, array $arguments)
    {
        $this->forwardCallTo($this->builder, $method, $arguments);

        return $this;
    }
}
