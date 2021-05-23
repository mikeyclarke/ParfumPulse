<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

class GtinNormalizer
{
    public static function normalize(string $gtin): string
    {
        return ltrim($gtin, '0');
    }
}
