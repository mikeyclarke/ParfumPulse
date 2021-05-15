<?php

declare(strict_types=1);

namespace ParfumPulse\Url;

class UrlIdGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(4));
    }
}
