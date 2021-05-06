<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Aggregations\Concerns;

trait WithMissing
{
    private ?string $missing = null;

    public function missing(string $missingValue): self
    {
        $this->missing = $missingValue;

        return $this;
    }
}
