<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use ParfumPulse\MerchantPage\MerchantPageCreator;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\MerchantPage\MerchantPageUpdater;
use ParfumPulse\Merchant\MerchantModel;

class MerchantPageLazyCreation
{
    public function __construct(
        private MerchantPageCreator $merchantPageCreator,
        private MerchantPageRepository $merchantPageRepository,
        private MerchantPageUpdater $merchantPageUpdater,
    ) {
    }

    public function createOrUpdate(MerchantModel $merchant, string $urlPath, array $parameters): MerchantPageModel
    {
        $result = $this->merchantPageRepository->findOneByUrlPath($urlPath, $merchant->getId());
        if (null === $result) {
            return $this->merchantPageCreator->create($merchant, array_merge($parameters, ['url_path' => $urlPath]));
        }

        $merchantPage = MerchantPageModel::createFromArray($result);
        if (empty(array_diff($parameters, $result))) {
            return $merchantPage;
        }

        return $this->merchantPageUpdater->update($merchantPage, $parameters);
    }
}
