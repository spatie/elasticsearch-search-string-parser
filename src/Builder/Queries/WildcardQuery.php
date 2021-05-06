<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class WildcardQuery implements Query
{
    private string $field;

    private string $value;

    public function __construct(
        string $field,
        string $value
    ) {
        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'wildcard' => [
                $this->field => [
                    'value' => $this->value,
                ],
            ],
        ];
    }
}
