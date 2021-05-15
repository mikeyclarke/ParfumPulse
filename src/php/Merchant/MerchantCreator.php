<?php

declare(strict_types=1);

namespace ParfumPulse\Merchant;

use ParfumPulse\Merchant\Exception\NoMerchantWithCodeException;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Merchant\MerchantStorage;

class MerchantCreator
{
    public function __construct(
        private MerchantStorage $storage,
        private array $merchantsConfig,
    ) {
    }

    public function create(string $code): MerchantModel
    {
        if (!isset($this->merchantsConfig[$code])) {
            throw new NoMerchantWithCodeException();
        }

        $result = $this->storage->insert($code);
        return MerchantModel::createFromArray($result);
    }
}
