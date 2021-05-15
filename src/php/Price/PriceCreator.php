<?php

declare(strict_types=1);

namespace ParfumPulse\Price;

use DateTime;
use ParfumPulse\Price\PriceStorage;
use ParfumPulse\Product\ProductModel;

class PriceCreator
{
    public function __construct(
        private PriceStorage $priceStorage,
    ) {
    }

    public function create(ProductModel $product, float $amount): void
    {
        $time = new DateTime();
        $this->priceStorage->insert($product->getId(), $amount, $time->format('Y-m-d H:i:s'));
    }
}
