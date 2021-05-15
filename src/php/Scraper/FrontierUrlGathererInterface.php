<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\UrlCollection;

interface FrontierUrlGathererInterface
{
    public function gather(MerchantModel $merchant): UrlCollection;
}
