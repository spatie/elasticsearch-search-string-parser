<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(
        public array $hits,
        public array $suggestions,
        public bool $isGrouped,
        public array $raw,
    ) {
    }
}
