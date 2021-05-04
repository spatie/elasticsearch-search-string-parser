<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(

    ) {
    }

    public static function from(array $results)
    {
        $hits = collect($results)->map(function () {

        });

        return new static($results, $hits);
    }
}
