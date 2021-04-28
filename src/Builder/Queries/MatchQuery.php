<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

use JetBrains\PhpStorm\ArrayShape;

class MatchQuery implements Query
{
    public function __construct(
        protected string $query,
        protected string $field,
        protected int $fuzziness = 2
    ) {
    }

    #[ArrayShape(['match' => 'array'])]
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
