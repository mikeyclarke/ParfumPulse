<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Scraper;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\MerchantPage\MerchantPageUpdater;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Price\PriceManager;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;
use ParfumPulse\Scraper\ScraperFailuresProcessor;

class ScraperFailuresProcessorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantPageRepository;
    private LegacyMockInterface $merchantPageUpdater;
    private LegacyMockInterface $priceManager;
    private LegacyMockInterface $productRepository;
    private int $maxFailedScrapeDays;
    private ScraperFailuresProcessor $scraperFailuresProcessor;

    public function setUp(): void
    {
        $this->merchantPageRepository = m::mock(MerchantPageRepository::class);
        $this->merchantPageUpdater = m::mock(MerchantPageUpdater::class);
        $this->priceManager = m::mock(PriceManager::class);
        $this->productRepository = m::mock(ProductRepository::class);
        $this->maxFailedScrapeDays = 4;

        $this->scraperFailuresProcessor = new ScraperFailuresProcessor(
            $this->merchantPageRepository,
            $this->merchantPageUpdater,
            $this->priceManager,
            $this->productRepository,
            $this->maxFailedScrapeDays,
        );
    }

    public function testProcess(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $failures = [
            '/foo/bar' => [
                'productIds' => [123, 456],
            ],
        ];

        $merchantPageArray = [
            'failed_scrape_days' => 0,
        ];

        $this->createMerchantPageRepositoryExpectation(['/foo/bar', $merchantId], $merchantPageArray);
        $this->createMerchantPageUpdaterExpectation(
            [m::type(MerchantPageModel::class), ['failed_scrape_days' => 1, 'should_scrape' => true]],
            new MerchantPageModel()
        );

        $this->scraperFailuresProcessor->process($merchant, $failures);
    }

    public function testProcessExpiresProductPricesAfterSecondFailedScrape(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $failures = [
            '/foo/bar' => [
                'productIds' => [123, 456],
            ],
        ];

        $merchantPageArray = [
            'failed_scrape_days' => 1,
        ];
        $product1Array = ['id' => 123];
        $product2Array = ['id' => 456];

        $this->createMerchantPageRepositoryExpectation(['/foo/bar', $merchantId], $merchantPageArray);
        $this->createProductRepositoryExpectation([123], $product1Array);
        $this->createPriceManagerExpectation([m::type(ProductModel::class), null, false]);
        $this->createProductRepositoryExpectation([456], $product2Array);
        $this->createPriceManagerExpectation([m::type(ProductModel::class), null, false]);
        $this->createMerchantPageUpdaterExpectation(
            [m::type(MerchantPageModel::class), ['failed_scrape_days' => 2, 'should_scrape' => true]],
            new MerchantPageModel()
        );

        $this->scraperFailuresProcessor->process($merchant, $failures);
    }

    public function testProcessStopsFutureCrawlingAfterMaxCrawls(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $failures = [
            '/foo/bar' => [
                'productIds' => [123, 456],
            ],
        ];

        $merchantPageArray = [
            'failed_scrape_days' => 3,
        ];

        $this->createMerchantPageRepositoryExpectation(['/foo/bar', $merchantId], $merchantPageArray);
        $this->createMerchantPageUpdaterExpectation(
            [m::type(MerchantPageModel::class), ['failed_scrape_days' => 4, 'should_scrape' => false]],
            new MerchantPageModel()
        );

        $this->scraperFailuresProcessor->process($merchant, $failures);
    }

    private function createMerchantPageRepositoryExpectation(array $args, array $result): void
    {
        $this->merchantPageRepository
            ->shouldReceive('findOneByUrlPath')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPriceManagerExpectation(array $args): void
    {
        $this->priceManager
            ->shouldReceive('registerPrice')
            ->once()
            ->with(...$args);
    }

    private function createProductRepositoryExpectation(array $args, array $result): void
    {
        $this->productRepository
            ->shouldReceive('findOneById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMerchantPageUpdaterExpectation(array $args, MerchantPageModel $result): void
    {
        $this->merchantPageUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
