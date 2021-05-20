<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

abstract class GroupDirective extends PatternDirective
{
    abstract public function transformToHits(array $results): array;
}
