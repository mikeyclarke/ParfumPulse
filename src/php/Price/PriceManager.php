<?php

declare(strict_types=1);

namespace ParfumPulse\Price;

use DateTime;
use ParfumPulse\Price\PriceCreator;
use ParfumPulse\Price\PriceModel;
use ParfumPulse\Price\PriceRepository;
use ParfumPulse\Price\PriceStorage;
use ParfumPulse\Product\ProductModel;

class PriceManager
{
    public function __construct(
        private PriceCreator $priceCreator,
        private PriceRepository $priceRepository,
        private PriceStorage $priceStorage,
    ) {
    }

    public function registerPrice(ProductModel $product, ?float $amount, bool $available = true): void
    {
        $result = $this->priceRepository->findCurrentPrice($product->getId());
        if (null === $result) {
            if (!$available || null === $amount) {
                return;
            }

            $this->priceCreator->create($product, $amount);
            return;
        }

        $currentPrice = PriceModel::createFromArray($result);
        if ($available && $amount === $currentPrice->getAmount()) {
            return;
        }

        $this->expirePrice($currentPrice);

        if (!$available || null === $amount) {
            return;
        }

        $this->priceCreator->create($product, $amount);
    }

    private function expirePrice(PriceModel $price): void
    {
        $time = new DateTime();
        $this->priceStorage->update($price->getId(), [
            'time_to' => $time->format('Y-m-d H:i:s'),
        ]);
    }
}
