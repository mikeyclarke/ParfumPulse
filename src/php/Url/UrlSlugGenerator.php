<?php

declare(strict_types=1);

namespace ParfumPulse\Url;

use Ausi\SlugGenerator\SlugGenerator;

class UrlSlugGenerator
{
    public const DEFAULT_MAX_LENGTH = 120;

    public function generate(string $text, int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $generator = new SlugGenerator();
        $result = $generator->generate($text);
        if (mb_strlen($result) > $maxLength) {
            return $this->trimToFit($result, $maxLength);
        }
        return $result;
    }

    private function trimToFit(string $slug, int $maxLength): string
    {
        $slug = mb_strimwidth($slug, 0, $maxLength);
        $slug = trim($slug, '-');
        return $slug;
    }
}
