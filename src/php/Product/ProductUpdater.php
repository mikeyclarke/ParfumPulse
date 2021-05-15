<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductStorage;
use ParfumPulse\Product\ProductValidator;

class ProductUpdater
{
    public function __construct(
        private ProductStorage $productStorage,
        private ProductValidator $productValidator,
    ) {
    }

    public function update(ProductModel $product, array $parameters): ProductModel
    {
        $this->productValidator->validate($parameters);

        $result = $this->productStorage->update($product->getId(), $parameters);
        $product->updateFromArray($result);

        return $product;
    }
}
