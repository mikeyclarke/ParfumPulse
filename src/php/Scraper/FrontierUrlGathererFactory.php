<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\FrontierUrlGathererInterface;
use ParfumPulse\Scraper\SitemapFrontierUrlGatherer;

class FrontierUrlGathererFactory
{
    public function __construct(
        private SitemapFrontierUrlGatherer $sitemapFrontierUrlGatherer,
    ) {
    }

    public function create(MerchantModel $merchant): FrontierUrlGathererInterface
    {
        return $this->sitemapFrontierUrlGatherer;
    }
}
