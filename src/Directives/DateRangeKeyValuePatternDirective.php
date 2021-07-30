<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use DateTimeImmutable;
use Exception;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchQueryBuilder\Queries\RangeQuery;

class DateRangeKeyValuePatternDirective extends PatternDirective
{
    public function __construct(protected string $key, protected string $field)
    {
    }

    public static function forField(string $key, string $field): static
    {
        return new static($key, $field);
    }

    public function apply(Builder $builder, string $pattern, array $values, int $patternOffsetStart, int $patternOffsetEnd): void
    {
        if (! $this->validateDateTime($values['value'])) {
            return;
        }

        $dateTime = new DateTimeImmutable($values['value']);
        $dateTimeString = $dateTime->format(DateTimeImmutable::ATOM);

        $rangeQuery = RangeQuery::create($this->field);

        match ($values['range'] ?? false) {
            '>' => $rangeQuery->gt($dateTimeString),
            '>=' => $rangeQuery->gte($dateTimeString),
            '<' => $rangeQuery->lt($dateTimeString),
            '<=' => $rangeQuery->lte($dateTimeString),
            default => null,
        };

        $builder->addQuery($rangeQuery, 'filter');
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<range>[<>]=?)(?<value>.*?)(?:$|\s)/i";
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
