<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Sorts;

class Sort
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    private string $field;

    private string $order;

    public function __construct(string $field, string $order)
    {
        $this->field = $field;
        $this->order = $order;
    }

    public function toArray(): array
    {
        return [
            $this->field => [
                'order' => $this->order,
            ],
        ];
    }
}
