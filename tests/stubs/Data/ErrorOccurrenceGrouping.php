<?php

namespace Spatie\ElasticsearchSearchStringParser\Tests\stubs\Data;

use DateTime;

class ErrorOccurrenceGrouping
{
    public function __construct(
        public DateTime $firstSeenAt,
        public DateTime $lastSeenAt,
        public int $occurrences
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            firstSeenAt: DateTime::createFromFormat('U', $payload['first_received_at']['value']),
            lastSeenAt: DateTime::createFromFormat('U', $payload['last_received_at']['value']),
            occurrences: $payload['doc_count'],
        );
    }
}
