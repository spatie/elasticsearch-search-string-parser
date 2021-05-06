<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class NestedQuery implements Query
{
    private string $path;

    private Query $query;

    public function __construct(
        string $path,
        Query $query
    ) {
        $this->path = $path;
        $this->query = $query;
    }

    public function toArray(): array
    {
        return [
            'nested' => [
                'path' => $this->path,
                'query' => $this->query->toArray(),
            ],
        ];
    }
}
