<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\stubs;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;
use Spatie\ElasticSearchQueryBuilder\Filters\GroupDirective;

class FlareGroupDirective extends GroupDirective
{
    protected array $allowedValues = [
        'exception_class' => ['exception_class', 'class'],
        'exception_message' => ['exception_message', 'message'],
        'seen_at_url' => ['seen_at_url', 'url'],
    ];

    public function canApply(string $pattern, array $values = []): bool
    {
        return in_array($values['value'], ['class', 'message', 'url']);
    }

    public function pattern(): string
    {
        return '/group:(?<value>.*?)(?:$|\s)/i';
    }

    public function apply(Builder $builder, string $pattern, array $values = []): static
    {
        $field = $this->getFieldForValue($values['value']);

        // apply aggregate for field

        return $this;
    }

    public function transformToHits(array $results): array
    {
        // TODO: Implement transformToHits() method.
    }

    protected function getFieldForValue($value): ?string
    {
        $allowed = array_filter(
            $this->allowedValues,
            fn(array $allowedValues, string $field) => in_array($value, $allowedValues),
            ARRAY_FILTER_USE_BOTH
        );

        return array_key_first($allowed);
    }
}

