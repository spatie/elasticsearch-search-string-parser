<?php

namespace Spatie\ElasticsearchSearchStringParser\Tests\stubs\Data;

use Spatie\ElasticsearchSearchStringParser\SearchHit;

class ErrorOccurrenceHit
{
    public function __construct(
        public ErrorOccurrence $errorOccurrence,
        public ?ErrorOccurrenceGrouping $errorOccurrenceGrouping = null
    ) {
    }

    public static function fromSearchHit(SearchHit $hit)
    {
        return new self(
            ErrorOccurrence::fromPayload($hit->data),
            $hit->groupingData ? ErrorOccurrenceGrouping::fromPayload($hit->groupingData) : null
        );
    }
}
