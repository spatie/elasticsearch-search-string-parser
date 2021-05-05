<?php

namespace Spatie\ElasticSearchQueryBuilder;

class SearchResults
{
    public function __construct(
        protected array $raw,
        protected array $hits,
    ) {
    }
}
