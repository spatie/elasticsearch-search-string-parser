<?php

namespace Spatie\ElasticSearchQueryBuilder\Tests\Support;

class PayloadFactory
{
    public static function hit(string $title, string $content): array
    {
        return [
            '_source' => [
                'title' => $title,
                'content' => $content,
            ],
        ];
    }

    public static function bucketAggregation(string $name, array ...$buckets): array
    {
        return [
            $name => [
                'doc_count_error_upper_bound' => 0,
                'sum_other_doc_count' => 0,
                'buckets' => $buckets,
            ],
        ];
    }

    public static function suggestionBucket(string $key): array
    {
        return [
            'key' => $key,
            'doc_count' => 0,
        ];
    }

    public static function groupingBucket(array $hit, $topHitAggregationName = 'top_hit'): array
    {
        return [
            $topHitAggregationName => [
                'hits' => [
                    'hits' => [
                        [
                            '_source' => $hit,
                        ],
                    ],
                ],
            ],
        ];
    }
}
