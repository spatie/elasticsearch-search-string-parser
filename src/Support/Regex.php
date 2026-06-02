<?php

namespace Spatie\ElasticsearchStringParser\Support;

class Regex
{
    /*
     * Like preg_match_all but corrects byte offsets to character
     * offsets when using the `PREG_OFFSET_CAPTURE` flag.
     */
    public static function mb_preg_match_all(
        $pattern,
        $subject,
        &$matches,
        $flags = PREG_PATTERN_ORDER,
        $offset = 0,
        $encoding = null
    ): int|bool {
        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }

        $offset = strlen(mb_substr($subject, 0, $offset, $encoding));
        $matchCount = preg_match_all($pattern, $subject, $matches, $flags, $offset);

        if ($matchCount && ($flags & PREG_OFFSET_CAPTURE)) {
            foreach ($matches as &$match) {
                foreach ($match as &$match) {
                    $match[1] = mb_strlen(substr($subject, 0, $match[1]), $encoding);
                }
            }
        }

        return $matchCount;
    }
}
