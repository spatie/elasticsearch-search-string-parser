<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class MatchQuery implements Query
{
    public static function create(
        string $field,
        string $query,
        int $fuzziness = 0
    ): self {
        return new self($field, $query, $fuzziness);
    }

    public function __construct(
        protected string $field,
        protected string $query,
        protected int $fuzziness = 0
    ) {
    }

    public function toArray(): array
    {
        return [
            'match' => [
                $this->field => [
                    'query' => $this->query,
                    'fuzziness' => $this->fuzziness,
                ],
            ],
        ];
    }
}
