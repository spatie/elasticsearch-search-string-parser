<?php

namespace Spatie\ElasticSearchQueryBuilder\Builder\Queries;

interface Query
{
    public function toArray(): array;
}
