<?php

namespace Spatie\ElasticsearchStringParser\Directives;

abstract class GroupDirective extends PatternDirective
{
    abstract public function transformToHits(array $results): array;
}
