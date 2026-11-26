<?php

namespace Tests\Unit;

use App\Support\RatingFormatter;
use PHPUnit\Framework\TestCase;

class RatingFormatterTest extends TestCase
{
    public function test_average_rating_keeps_precision_without_rounding(): void
    {
        $this->assertSame('4.7', RatingFormatter::formatAverage(4.75));
        $this->assertSame('4.3', RatingFormatter::formatAverage(4.33339));
        $this->assertSame('4.0', RatingFormatter::formatAverage(4));
    }
}
