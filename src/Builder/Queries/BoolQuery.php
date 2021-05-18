<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

use JetBrains\PhpStorm\ArrayShape;
use Spatie\ElasticSearchQueryBuilder\Builder\Exceptions\BoolQueryTypeDoesNotExist;

class BoolQuery implements Query
{
    protected array $must = [];
    protected array $filter = [];
    protected array $should = [];
    protected array $mustNot = [];

    public static function create(): static
    {
        return new self();
    }

    public function add(Query $query, string $type = 'must'): static
    {
        if (! in_array($type, ['must', 'filter', 'should', 'mustNot'])) {
            throw new BoolQueryTypeDoesNotExist($type);
        }

        $this->$type[] = $query;

        return $this;
    }

    #[ArrayShape(['bool' => "array"])]
    public function toArray(): array
    {
        $bool = [
            'must' => array_map(fn (Query $query) => $query->toArray(), $this->must),
            'filter' => array_map(fn (Query $query) => $query->toArray(), $this->filter),
            'should' => array_map(fn (Query $query) => $query->toArray(), $this->should),
            'must_not' => array_map(fn (Query $query) => $query->toArray(), $this->mustNot),
        ];

        return [
            'bool' => array_filter($bool),
        ];
    }
}
