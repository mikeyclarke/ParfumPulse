<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\AllbeautyScraper;
use ParfumPulse\Scraper\NotinoScraper;
use ParfumPulse\Scraper\ScraperBot;
use ParfumPulse\Scraper\ScraperInterface;

class ScraperFactory
{
    public function __construct(
        private AllbeautyScraper $allbeautyScraper,
        private NotinoScraper $notinoScraper,
        private array $merchantsConfig,
    ) {
    }

    public function create(MerchantModel $merchant): ScraperInterface
    {
        $merchantCode = $merchant->getCode();

        switch ($merchantCode) {
            case 'allbeauty':
                $scraper = $this->allbeautyScraper;
                break;
            case 'notino':
                $scraper = $this->notinoScraper;
                break;
            default:
                throw new \RuntimeException('Unsupported merchant.');
        }

        $scraper->setBot(new ScraperBot($this->merchantsConfig[$merchantCode]['url']));

        return $scraper;
    }
}
