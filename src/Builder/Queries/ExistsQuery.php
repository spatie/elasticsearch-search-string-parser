<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

use JetBrains\PhpStorm\ArrayShape;

class ExistsQuery implements Query
{
    public static function create(
        string $field
    ): self {
        return new self($field);
    }

    public function __construct(
        protected string $field
    ) {
    }

    #[ArrayShape(['match' => 'array'])]
    public function toArray(): array
    {
        return [
            'exists' => [
                'field' => $this->field,
            ],
        ];
    }
}
