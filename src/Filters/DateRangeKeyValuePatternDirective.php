<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Cassandra\Date;
use DateTimeImmutable;
use Exception;
use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\TermsAggregation;
use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MatchQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\MultiMatchQuery;
use Spatie\ElasticSearchQueryBuilder\Builder\Queries\RangeQuery;

class DateRangeKeyValuePatternDirective extends PatternDirective
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
        if (!$this->validateDateTime($values['value'])) {
            return;
        }

        $dateTime = new DateTimeImmutable($values['value']);
        $dateTimeString = $dateTime->format(DateTimeImmutable::ATOM);

        $rangeQuery = RangeQuery::create($this->field);

        match($values['range'] ?? false) {
            '>' => $rangeQuery->gt($dateTimeString),
            '>=' => $rangeQuery->gte($dateTimeString),
            '<' => $rangeQuery->lt($dateTimeString),
            '<=' => $rangeQuery->lte($dateTimeString),
            default => null,
        };

        $builder->addQuery($rangeQuery);
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<range>\>|\>=|\<|\<=)(?<value>.*?)(?:$|\s)/ig";
    }

    protected function validateDateTime(string $value): bool
    {
        try {
            new DateTimeImmutable($value);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
