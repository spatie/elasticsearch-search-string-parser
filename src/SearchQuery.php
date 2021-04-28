<?php

namespace Spatie\ElasticSearchQueryBuilder;

use Elasticsearch\Client;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\Filter;

class SearchQuery
{
    /** @var \Spatie\ElasticSearchQueryBuilder\Filters\Filter[] */
    protected array $filters;

    public function __construct(protected Builder $builder)
    {
    }

    public static function forClient(Client $client): static
    {
        $builder = new Builder();

        return new static($builder);
    }

    public function filters(Filter ...$filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function query(string $query): static
    {
        collect($this->filters)
            ->reduce(function (string $query, Filter $filter) {
                $filter->apply($this->builder);
            }, $query);

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
