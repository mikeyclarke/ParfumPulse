<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Scraper\ScraperBot;
use ParfumPulse\Scraper\ScraperResult;

interface ScraperInterface
{
    public function setBot(ScraperBot $bot): void;

    public function scrape(string $url, array $props): ScraperResult;
}
