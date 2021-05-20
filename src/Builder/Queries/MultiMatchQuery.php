<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class MultiMatchQuery implements Query
{
    public static function create(
        string $query,
        array $fields,
        int|string $fuzziness = 'AUTO'
    ): static {
        return new self($query, $fields, $fuzziness);
    }

    public function __construct(
        protected string $query,
        protected array $fields,
        protected int|string $fuzziness = 'AUTO'
    ) {
    }

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
