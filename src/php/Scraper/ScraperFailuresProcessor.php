<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\MerchantPage\MerchantPageUpdater;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Price\PriceManager;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;

class ScraperFailuresProcessor
{
    public function __construct(
        private MerchantPageRepository $merchantPageRepository,
        private MerchantPageUpdater $merchantPageUpdater,
        private PriceManager $priceManager,
        private ProductRepository $productRepository,
        private int $maxFailedScrapeDays,
    ) {
    }

    public function process(MerchantModel $merchant, array $failures): void
    {
        $merchantId = $merchant->getId();
        foreach ($failures as $url => $props) {
            $result = $this->merchantPageRepository->findOneByUrlPath($url, $merchantId);
            if (null === $result) {
                continue;
            }
            $merchantPage = MerchantPageModel::createFromArray($result);

            if (!empty($props['productIds']) && $merchantPage->getFailedScrapeDays() === 1) {
                foreach ($props['productIds'] as $id) {
                    $result = $this->productRepository->findOneById($id);
                    if (null === $result) {
                        throw new \Exception('Product missing.');
                    }
                    $product = ProductModel::createFromArray($result);
                    $this->priceManager->registerPrice($product, null, false);
                }
            }

            $failedScrapeDays = $merchantPage->getFailedScrapeDays() + 1;
            $shouldScrape = true;
            if ($failedScrapeDays >= $this->maxFailedScrapeDays) {
                $shouldScrape = false;
            }

            $this->merchantPageUpdater->update($merchantPage, [
                'failed_scrape_days' => $failedScrapeDays,
                'should_scrape' => $shouldScrape,
            ]);
        }
    }
}
