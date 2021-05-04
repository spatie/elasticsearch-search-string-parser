<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

abstract class GroupDirective extends PatternDirective
{
    public function transformToHits(array $results): array
    {
        // TODO: Transform default results to hits
        return $results;
    }
}
