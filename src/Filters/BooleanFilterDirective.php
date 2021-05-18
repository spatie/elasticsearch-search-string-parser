<?php

namespace Spatie\ElasticSearchQueryBuilder\Filters;

use Spatie\ElasticSearchQueryBuilder\Builder\Builder;

class BooleanFilterDirective extends PatternDirective
{
    public function canApply(string $pattern, array $values = []): bool
    {
        if (count($values) > 1) {
            return false;
        }
    }

    public function apply(Builder $builder, string $pattern, array $values = []): void
    {
        /**
         * blabla snoozed
         * blabla snoozed:false
         * blabla snoozed:true
         * blabla snoozed:0
         * blabla snoozed:1
         *
         *
         * bla bla exception:'hello world'
         * bla bla exception:"hello world"
         * bla bla exception:hello
         *
         * blb bl date<=:12-05-2020
         * blb bl date<:12-05-2020
         * blb bl date>=:12-05-2020
         * blb bl date>:12-05-2020
         */

        if(count($values)){

        }
    }

    public function pattern(): string
    {
        return "/{$this->key}:(?<value>.*?)(?:\$|\\s)/i";
    }
}
