<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(
        public array $raw,
        public array $hits,
        public array $directives = []
    ) {
    }

    public static function from(array $results)
    {
        $hits = collect($results)->map(function () {

        });

        return new static($results, $hits);
    }
}
