<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use DateTimeImmutable;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MatchQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\RangeQuery;

class DateKeyValuePatternDirective extends PatternDirective
{
    public function __construct(protected string $key, protected string $field)
    {
    }

    public static function forField(string $key, string $field): static
    {
        return new static($key, $field);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function apply(Builder $builder, string $pattern, array $values = []): void
    {
        if (!$this->validateDate($values['value'])) {
            return;
        }

        $value = DateTimeImmutable::createFromFormat('Y-m-d', $values['value']);
        $day = $value->format('Y-m-d');

        $builder->addQuery(
            RangeQuery::create($this->field)
                ->gte("{$day}||/d")
                ->lt("{$day}||+1d/d")
        );
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<value>\d{4}\-\d{2}-\d{2})(?:\$|\\s)/i";
    }

    protected function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateTime = DateTimeImmutable::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) === $date;
    }
}
