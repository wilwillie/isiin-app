<?php

namespace App\Support;

class RatingFormatter
{
    public static function formatAverage(float|int|string $rating): string
    {
        $truncated = floor((float) $rating * 10) / 10;

        return number_format($truncated, 1, '.', ',');
    }
}
