<?php

namespace Spatie\ElasticsearchStringParser\Directives;

use DateTimeImmutable;
use Spatie\ElasticsearchQueryBuilder\Builder;
use Spatie\ElasticsearchQueryBuilder\Queries\RangeQuery;

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
        $value = $this->parseDate($values['value']);

        if (! $value) {
            return;
        }

        $day = $value->format('Y-m-d');

        $builder->addQuery(
            RangeQuery::create($this->field)
                ->gte("{$day}||/d")
                ->lt("{$day}||+1d/d"),
            'filter'
        );
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<value>\d{4}\-\d{2}-\d{2}|today)(?:$|\s)/i";
    }

    protected function parseDate(string $date, string $format = 'Y-m-d'): ?DateTimeImmutable
    {
        if ($date === 'today') {
            return new DateTimeImmutable();
        }

        $dateTime = DateTimeImmutable::createFromFormat($format, $date);

        if (! $dateTime || $dateTime->format($format) !== $date) {
            return null;
        }

        return $dateTime;
    }
}
