<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Typography\StringNormalizer;

class BrandNameNormalizer
{
    public function __construct(
        private StringNormalizer $stringNormalizer,
        private array $brandNameAliases,
    ) {
    }

    public function normalize(string $name): string
    {
        $normalized = $this->stringNormalizer->normalize($name);
        if (isset($this->brandNameAliases[$normalized])) {
            return $this->brandNameAliases[$normalized];
        }
        return $normalized;
    }
}
