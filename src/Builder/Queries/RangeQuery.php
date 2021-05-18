<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

class RangeQuery implements Query
{
    protected null|int|float $gte = null;

    protected null|int|float $lt = null;

    protected null|int|float $lte = null;

    protected null|int|float $gt = null;

    public static function create(string $field): self
    {
        return new self($field);
    }

    public function __construct(protected string $field)
    {
    }

    public function lt(int|float $value): self
    {
        $this->lt = $value;

        return $this;
    }

    public function lte(int|float $value): self
    {
        $this->lte = $value;

        return $this;
    }

    public function gt(int|float $value): self
    {
        $this->gt = $value;

        return $this;
    }

    public function gte(int|float $value): self
    {
        $this->gte = $value;

        return $this;
    }

    public function toArray(): array
    {
        $parameters = [];

        if ($this->lt !== null) {
            $parameters['lt'] = $this->lt;
        }

        if ($this->lte !== null) {
            $parameters['lte'] = $this->lte;
        }

        if ($this->gt !== null) {
            $parameters['gt'] = $this->gt;
        }

        if ($this->gte !== null) {
            $parameters['gte'] = $this->gte;
        }

        return [
            'range' => [
                $this->field => $parameters,
            ],
        ];
    }
}
