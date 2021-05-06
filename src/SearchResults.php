<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(
        protected array $hits,
        protected array $raw,
    ) {
    }
}
