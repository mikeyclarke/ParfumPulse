<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageStorage;
use ParfumPulse\MerchantPage\MerchantPageValidator;

class MerchantPageUpdater
{
    public function __construct(
        private MerchantPageStorage $merchantPageStorage,
        private MerchantPageValidator $merchantPageValidator,
    ) {
    }

    public function update(MerchantPageModel $merchantPage, array $parameters): MerchantPageModel
    {
        $this->merchantPageValidator->validate($parameters);

        $result = $this->merchantPageStorage->update($merchantPage->getId(), $parameters);
        $merchantPage->updateFromArray($result);

        return $merchantPage;
    }
}
