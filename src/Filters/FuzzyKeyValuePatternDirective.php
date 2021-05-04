<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;

class FuzzyKeyValuePatternDirective extends PatternDirective
{
    public function __construct(protected string $key, protected array $fields)
    {
    }

    public static function forField(string $key, string $field): static
    {
        return new static($key, [$field]);
    }

    public static function forFields(string $key, string ...$fields): static
    {
        return new static($key, $fields);
    }

    public function apply(Builder $builder, string $pattern, array $values = [])
    {
        $query = new MultiMatchQuery($values['value'], $this->fields);

        $builder->addQuery($query);
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<value>.*?)(?:\$|\\s)/i";
    }
}
