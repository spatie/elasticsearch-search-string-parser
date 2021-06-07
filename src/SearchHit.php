<?php

namespace Spatie\ElasticsearchStringParser;

class SearchHit
{
    public function __construct(
        public array $data,
        public ?array $groupingData = null
    ) {
    }
}
