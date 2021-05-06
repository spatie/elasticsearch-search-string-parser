<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\stubs\Data;

use DateTime;

class ErrorOccurrence
{
    public function __construct(
        public int $id,
        public string $exceptionClass,
        public string $exceptionMessage,
        public string $applicationPath,
        public string $stage,
        public DateTime $receivedAt,
    ) {
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            id: $payload['id'],
            exceptionClass: $payload['exception_class'],
            exceptionMessage: $payload['exception_message'],
            applicationPath: $payload['application_path'],
            stage: $payload['stage'],
            receivedAt: DateTime::createFromFormat(DATE_ATOM, $payload['received_at'])
        );
    }
}
