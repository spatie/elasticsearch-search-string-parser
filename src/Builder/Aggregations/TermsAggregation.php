<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations;

use JetBrains\PhpStorm\ArrayShape;

class TermsAggregation extends Aggregation
{
    private string $field;

    private ?int $size = null;

    private ?array $metaData = null;

    private ?string $missing = null;

    private ?array $order = null;

    private AggregationCollection $aggregations;

    public static function create(string $name, string $field): self
    {
        return new self($name, $field);
    }

    public function __construct(string $name, string $field)
    {
        $this->name = $name;
        $this->field = $field;
        $this->aggregations = new AggregationCollection();
    }

    public function aggregation(Aggregation $aggregation): self
    {
        $this->aggregations->add($aggregation);

        return $this;
    }

    public function size(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function metaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function missing(string $missingValue): self
    {
        $this->missing = $missingValue;

        return $this;
    }

    public function order(array $order): self
    {
        $this->order = $order;

        return $this;
    }

    #[ArrayShape([
        'terms' => "string[]",
        'meta' => "mixed",
        'aggs' => "mixed"
    ])]
    public function toArray(): array
    {
        $parameters = [
            'field' => $this->field,
        ];

        if ($this->size) {
            $parameters['size'] = $this->size;
        }

        if ($this->missing) {
            $parameters['missing'] = $this->missing;
        }

        if ($this->order) {
            $parameters['order'] = $this->order;
        }

        $aggregation = [
            'terms' => $parameters,
        ];

        if (! $this->aggregations->isEmpty()) {
            $aggregation['aggs'] = $this->aggregations->toArray();
        }

        if ($this->metaData !== null) {
            $aggregation['meta'] = $this->metaData;
        }

        return $aggregation;
    }
}
