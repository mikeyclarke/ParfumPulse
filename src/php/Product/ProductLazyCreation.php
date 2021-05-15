<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Product\ProductCreator;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;
use ParfumPulse\Product\ProductUpdater;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Variant\VariantModel;

class ProductLazyCreation
{
    public function __construct(
        private ProductCreator $productCreator,
        private ProductRepository $productRepository,
        private ProductUpdater $productUpdater,
    ) {
    }

    public function createOrUpdate(
        MerchantModel $merchant,
        VariantModel $variant,
        MerchantPageModel $merchantPage,
        string $urlPath,
        array $parameters
    ): ProductModel {
        $result = $this->productRepository->findOneBy([
            'merchant_id' => $merchant->getId(),
            'variant_id' => $variant->getId(),
        ]);
        if (null === $result) {
            return $this->productCreator->create(
                $merchant,
                $variant,
                $merchantPage,
                array_merge($parameters, ['url_path' => $urlPath])
            );
        }

        $product = ProductModel::createFromArray($result);
        if (empty(array_diff($parameters, $result))) {
            return $product;
        }

        return $this->productUpdater->update($product, $parameters);
    }
}
