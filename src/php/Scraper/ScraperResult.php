<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

class ScraperResult
{
    public function __construct(
        private string $urlPath,
        private array $existingPageProductIds = [],
        private ?array $scrapedBrand = null,
        private ?array $scrapedFragrance = null,
        private array $scrapedVariants = [],
        private bool $isRelevant = true
    ) {
    }

    public function isRelevant(): bool
    {
        return $this->isRelevant;
    }

    public function getPageUrlPath(): string
    {
        return $this->urlPath;
    }

    public function pageHasExistingProducts(): bool
    {
        return !empty($this->existingPageProductIds);
    }

    public function getExistingPageProductIds(): array
    {
        return $this->existingPageProductIds;
    }

    public function getScrapedBrand(): ?array
    {
        return $this->scrapedBrand;
    }

    public function getScrapedFragrance(): ?array
    {
        return $this->scrapedFragrance;
    }

    public function getScrapedVariants(): array
    {
        return $this->scrapedVariants;
    }
}
