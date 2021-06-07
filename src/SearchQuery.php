<?php

namespace Spatie\ElasticsearchSearchStringParser;

use Elasticsearch\Client;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchSearchStringParser\Concerns\ForwardsCalls;
use Spatie\ElasticsearchSearchStringParser\Directives\BaseDirective;
use Spatie\ElasticsearchSearchStringParser\Directives\GroupDirective;
use Spatie\ElasticsearchSearchStringParser\Directives\PatternDirective;

/** @mixin \Spatie\ElasticsearchQueryBuilder\Builder */
class SearchQuery
{
    use ForwardsCalls;

    /** @var \Spatie\ElasticsearchSearchStringParser\Directives\PatternDirective[] */
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
     * @param \Spatie\ElasticsearchSearchStringParser\Directives\BaseDirective $filter
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
