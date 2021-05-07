<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

use JetBrains\PhpStorm\ArrayShape;

class MultiMatchQuery implements Query
{
    public static function create(
        string $query,
        array $fields,
        int $fuzziness = 2
    ): static {
        return new self($query, $fields, $fuzziness);
    }

    public function __construct(
        protected string $query,
        protected array $fields,
        protected int $fuzziness = 2
    ) {
    }

    #[ArrayShape(['multi_match' => 'array'])]
    public function toArray(): array
    {
        return [
            'multi_match' => [
                'query' => $this->query,
                'fields' => $this->fields,
                'fuzziness' => $this->fuzziness,
            ],
        ];
    }
}
