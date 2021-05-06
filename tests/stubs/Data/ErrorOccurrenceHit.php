<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\stubs\Data;

class ErrorOccurrenceHit
{
    public function __construct(
        public ErrorOccurrence $errorOccurrence,
        public ?ErrorOccurrenceGrouping $errorOccurrenceGrouping = null
    )
    {
    }
}
