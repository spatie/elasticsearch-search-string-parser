<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

use Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Concerns\WithMissing;

class MinAggregation extends Aggregation
{
    use WithMissing;

    private string $field;

    public function __construct(string $name, string $field)
    {
        $this->name = $name;
        $this->field = $field;
    }

    public function toArray(): array
    {
        $parameters = [
            'field' => $this->field,
        ];

        if ($this->missing) {
            $parameters['missing'] = $this->missing;
        }

        return [
            'min' => $parameters
        ];
    }
}
