<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use Doctrine\DBAL\Connection;
use ParfumPulse\Brand\BrandLazyCreation;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceLazyCreation;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\MerchantPage\MerchantPageLazyCreation;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Price\PriceManager;
use ParfumPulse\Product\ProductLazyCreation;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;
use ParfumPulse\Scraper\UrlIgnoreList;
use ParfumPulse\Variant\VariantLazyCreation;
use ParfumPulse\Variant\VariantModel;

class ScraperResultsProcessor
{
    public function __construct(
        private BrandLazyCreation $brandLazyCreation,
        private Connection $connection,
        private FragranceLazyCreation $fragranceLazyCreation,
        private MerchantPageLazyCreation $merchantPageLazyCreation,
        private PriceManager $priceManager,
        private ProductLazyCreation $productLazyCreation,
        private ProductRepository $productRepository,
        private UrlIgnoreList $urlIgnoreList,
        private VariantLazyCreation $variantLazyCreation,
    ) {
    }

    public function process(MerchantModel $merchant, array $results): void
    {
        $urlsToIgnore = [];

        foreach ($results as $url => $result) {
            if (!$result->isRelevant()) {
                $urlsToIgnore[] = $result->getPageUrlPath();
                continue;
            }

            $this->connection->beginTransaction();
            try {
                $merchantPage = $this->createOrUpdatePage($result, $merchant);
                $brand = $this->createOrRetrieveBrand($result);
                $fragrance = $this->createOrRetrieveFragrance($result, $brand);

                $variants = $result->getScrapedVariants();
                $products = [];
                foreach ($variants as $props) {
                    $variant = $this->createOrRetrieveVariant($fragrance, $props);
                    $products[] = $this->createOrUpdateProduct($merchant, $merchantPage, $variant, $props);
                }

                if ($result->pageHasExistingProducts()) {
                    $existingProductIds = $result->getExistingPageProductIds();
                    $this->deactivateRemovedProducts($existingProductIds, $products);
                }

                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        }

        if (!empty($urlsToIgnore)) {
            $this->urlIgnoreList->add($merchant, $urlsToIgnore);
        }
    }

    private function deactivateRemovedProducts(array $existingProductIds, array $products): void
    {
        foreach ($existingProductIds as $id) {
            $matches = array_filter(
                $products,
                function (ProductModel $product) use ($id) {
                    return $product->getId() === $id;
                }
            );
            if (empty($matches)) {
                $result = $this->productRepository->findOneById($id);
                if (null === $result) {
                    throw new \Exception('Product missing.');
                }
                $product = ProductModel::createFromArray($result);
                $this->priceManager->registerPrice($product, null, false);
            }
        }
    }

    private function createOrUpdateProduct(
        MerchantModel $merchant,
        MerchantPageModel $merchantPage,
        VariantModel $variant,
        array $parameters
    ): ProductModel {
        $product = $this->productLazyCreation->createOrUpdate(
            $merchant,
            $variant,
            $merchantPage,
            $parameters['url_path'],
            [
                'free_delivery' => $parameters['free_delivery'],
            ]
        );
        $this->priceManager->registerPrice($product, $parameters['amount'], $parameters['available']);
        return $product;
    }

    private function createOrRetrieveVariant(FragranceModel $fragrance, array $parameters): VariantModel
    {
        return $this->variantLazyCreation->createOrRetrieve($fragrance, $parameters['name'], $parameters['gtin']);
    }

    private function createOrRetrieveFragrance(ScraperResult $result, BrandModel $brand): FragranceModel
    {
        $fragrance = $result->getScrapedFragrance();
        return $this->fragranceLazyCreation->createOrRetrieve(
            $brand,
            // @phpstan-ignore-next-line
            $fragrance['name'],
            // @phpstan-ignore-next-line
            $fragrance['gender'],
            // @phpstan-ignore-next-line
            $fragrance['type'],
        );
    }

    private function createOrRetrieveBrand(ScraperResult $result): BrandModel
    {
        $brand = $result->getScrapedBrand();
        // @phpstan-ignore-next-line
        return $this->brandLazyCreation->createOrRetrieve($brand['name']);
    }

    private function createOrUpdatePage(ScraperResult $result, MerchantModel $merchant): MerchantPageModel
    {
        $urlPath = $result->getPageUrlPath();
        return $this->merchantPageLazyCreation->createOrUpdate($merchant, $urlPath, [
            'failed_scrape_days' => 0,
            'should_scrape' => true,
        ]);
    }
}
