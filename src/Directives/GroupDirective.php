<?php

namespace Spatie\ElasticSearchQueryBuilder\Directives;

abstract class GroupDirective extends PatternDirective
{
    abstract public function transformToHits(array $results): array;
}
