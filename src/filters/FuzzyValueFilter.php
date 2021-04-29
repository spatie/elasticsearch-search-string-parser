<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;

class FuzzyValueFilter implements ValueFilter
{
    public function __construct(protected array $fields)
    {
    }

    public static function forField(string $field): static
    {
        return new static([$field]);
    }

    public static function forFields(string ...$fields): static
    {
        return new static($fields);
    }


    public function apply(Builder $builder, string $value)
    {
        $query = new MultiMatchQuery($value, $this->fields);

        $builder->addQuery($query);
    }
}
