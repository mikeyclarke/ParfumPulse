<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageStorage;
use ParfumPulse\MerchantPage\MerchantPageValidator;
use ParfumPulse\Merchant\MerchantModel;

class MerchantPageCreator
{
    public function __construct(
        private MerchantPageStorage $merchantPageStorage,
        private MerchantPageValidator $merchantPageValidator,
    ) {
    }

    public function create(MerchantModel $merchant, array $parameters): MerchantPageModel
    {
        $this->merchantPageValidator->validate($parameters, true);

        $result = $this->merchantPageStorage->insert(
            $parameters['url_path'],
            $merchant->getId(),
            $parameters['should_scrape'] ?? true,
            $parameters['failed_scrape_days'] ?? 0,
        );
        return MerchantPageModel::createFromArray($result);
    }
}
