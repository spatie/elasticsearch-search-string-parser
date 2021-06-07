<?php

namespace Spatie\ElasticsearchSearchStringParser\Directives;

abstract class GroupDirective extends PatternDirective
{
    abstract public function transformToHits(array $results): array;
}
