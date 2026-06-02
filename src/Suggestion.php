<?php

namespace Spatie\ElasticsearchStringParser;

class Suggestion
{
    public function __construct(public string $suggestion, public ?int $count = null) {}

    public static function fromBucket(array $bucket): self
    {
        return new self($bucket['key'], $bucket['doc_count'] ?? null);
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}
