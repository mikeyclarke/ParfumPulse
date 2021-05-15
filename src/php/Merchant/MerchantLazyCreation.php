<?php

declare(strict_types=1);

namespace ParfumPulse\Merchant;

use ParfumPulse\Merchant\MerchantCreator;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Merchant\MerchantRepository;

class MerchantLazyCreation
{
    public function __construct(
        private MerchantCreator $merchantCreator,
        private MerchantRepository $merchantRepository,
    ) {
    }

    public function createOrRetrieve(string $merchantCode): MerchantModel
    {
        $result = $this->merchantRepository->findOneByCode($merchantCode);
        if (null === $result) {
            return $this->merchantCreator->create($merchantCode);
        }

        return MerchantModel::createFromArray($result);
    }
}
