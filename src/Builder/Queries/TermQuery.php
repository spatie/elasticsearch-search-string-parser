<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class TermQuery implements Query
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
            'term' => [
                $this->field => $this->value,
            ],
        ];
    }
}
