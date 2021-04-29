<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\PatternFilter;
use Spatie\ElasticSearchQueryBuilder\Filters\ValueFilter;

class SearchQuery
{
    /** @var \Spatie\ElasticSearchQueryBuilder\Filters\PatternFilter[] */
    protected array $filters = [];

    protected ?ValueFilter $baseFilter = null;

    public function __construct(protected Builder $builder)
    {
    }

    public static function forClient(Client $client): static
    {
        $builder = new Builder();

        return new static($builder);
    }

    /**
     * This filter will be used to filter the remaining search query after all
     * pattern filters have been applied.
     *
     * @param \Spatie\ElasticSearchQueryBuilder\Filters\ValueFilter $filter
     *
     * @return $this
     */
    public function defaultFilter(ValueFilter $filter): static
    {
        $this->baseFilter = $filter;

        return $this;
    }

    public function patternFilters(PatternFilter ...$filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function query(string $query): static
    {
        $queryWithoutFilters = collect($this->filters)
            ->reduce(function (string $query, PatternFilter $filter) {
                $matches = preg_match_all($filter->pattern(), $query, $filters, PREG_SET_ORDER);

                if (! $matches) {
                    return $query;
                }

                collect($matches)->each(fn (array $match) => $filter->apply($this->builder, array_shift($match), $match));

                return preg_filter($filter->pattern(), '', $query);
            }, $query);

        if($this->baseFilter) {
            $this->baseFilter->apply($this->builder, $queryWithoutFilters);
        }

        return $this;
    }

    public function get(): ResultsCollection
    {
        return new ResultsCollection();
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }
}
