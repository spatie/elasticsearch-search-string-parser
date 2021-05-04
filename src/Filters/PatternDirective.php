<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

abstract class PatternDirective extends Directive
{
    abstract public function pattern(): string;
}
