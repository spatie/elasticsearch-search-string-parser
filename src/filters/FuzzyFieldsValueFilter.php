<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;

class FuzzyFieldsValueFilter implements ValueFilter
{
    public function __construct(protected array $fields)
    {
    }

    public function apply(Builder $builder, string $value)
    {
        $query = new MultiMatchQuery($value, $this->fields);

        $builder->addQuery($query);
    }
}
