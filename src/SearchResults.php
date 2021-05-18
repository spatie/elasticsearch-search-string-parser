<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(
        public array $hits,
        public array $suggestions,
        public array $raw,
    ) {
    }
}
