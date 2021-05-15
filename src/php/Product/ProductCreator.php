<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductStorage;
use ParfumPulse\Product\ProductValidator;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Variant\VariantModel;

class ProductCreator
{
    public function __construct(
        private ProductStorage $productStorage,
        private ProductValidator $productValidator,
    ) {
    }

    public function create(
        MerchantModel $merchant,
        VariantModel $variant,
        MerchantPageModel $merchantPage,
        array $parameters
    ): ProductModel {
        $this->productValidator->validate($parameters, true);

        $result = $this->productStorage->insert(
            $parameters['url_path'],
            $variant->getId(),
            $merchantPage->getId(),
            $merchant->getId(),
            $parameters['free_delivery'] ?? false,
        );

        $product = ProductModel::createFromArray($result);

        return $product;
    }
}
