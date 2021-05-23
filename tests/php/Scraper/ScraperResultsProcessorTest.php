<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Scraper;

use Doctrine\DBAL\Connection;
use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandLazyCreation;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceLazyCreation;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceGender;
use ParfumPulse\Fragrance\FragranceRepository;
use ParfumPulse\Fragrance\FragranceType;
use ParfumPulse\MerchantPage\MerchantPageLazyCreation;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Price\PriceManager;
use ParfumPulse\Product\ProductLazyCreation;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;
use ParfumPulse\Scraper\ScraperResult;
use ParfumPulse\Scraper\ScraperResultsProcessor;
use ParfumPulse\Scraper\UrlIgnoreList;
use ParfumPulse\Variant\VariantLazyCreation;
use ParfumPulse\Variant\VariantModel;
use ParfumPulse\Variant\VariantRepository;

class ScraperResultsProcessorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandLazyCreation;
    private LegacyMockInterface $connection;
    private LegacyMockInterface $fragranceLazyCreation;
    private LegacyMockInterface $fragranceRepository;
    private LegacyMockInterface $merchantPageLazyCreation;
    private LegacyMockInterface $priceManager;
    private LegacyMockInterface $productLazyCreation;
    private LegacyMockInterface $productRepository;
    private LegacyMockInterface $urlIgnoreList;
    private LegacyMockInterface $variantLazyCreation;
    private LegacyMockInterface $variantRepository;
    private ScraperResultsProcessor $scraperResultsProcessor;

    public function setUp(): void
    {
        $this->brandLazyCreation = m::mock(BrandLazyCreation::class);
        $this->connection = m::mock(Connection::class);
        $this->fragranceLazyCreation = m::mock(FragranceLazyCreation::class);
        $this->fragranceRepository = m::mock(FragranceRepository::class);
        $this->merchantPageLazyCreation = m::mock(MerchantPageLazyCreation::class);
        $this->priceManager = m::mock(PriceManager::class);
        $this->productLazyCreation = m::mock(ProductLazyCreation::class);
        $this->productRepository = m::mock(ProductRepository::class);
        $this->urlIgnoreList = m::mock(UrlIgnoreList::class);
        $this->variantLazyCreation = m::mock(VariantLazyCreation::class);
        $this->variantRepository = m::mock(VariantRepository::class);

        $this->scraperResultsProcessor = new ScraperResultsProcessor(
            $this->brandLazyCreation,
            $this->connection,
            $this->fragranceLazyCreation,
            $this->fragranceRepository,
            $this->merchantPageLazyCreation,
            $this->priceManager,
            $this->productLazyCreation,
            $this->productRepository,
            $this->urlIgnoreList,
            $this->variantLazyCreation,
            $this->variantRepository,
        );
    }

    public function testProcess(): void
    {
        $merchant = new MerchantModel();
        $results = [
            new ScraperResult('/foo/bar', isRelevant: false),
            new ScraperResult(
                '/foo/baz',
                scrapedBrand: ['name' => 'Moschino'],
                scrapedFragrance: [
                    'name' => 'Toy Boy',
                    'gender' => FragranceGender::MALE,
                    'type' => FragranceType::EAU_DE_PARFUM,
                ],
                scrapedVariants: [
                    [
                        'name' => '100 ml',
                        'gtin' => null,
                        'free_delivery' => false,
                        'amount' => null,
                        'available' => false,
                        'url_path' => '/foo/baz/p-16007859/',
                    ],
                    [
                        'name' => '50 ml',
                        'gtin' => null,
                        'free_delivery' => false,
                        'amount' => null,
                        'available' => false,
                        'url_path' => '/foo/baz/p-16006251/',
                    ],
                    [
                        'name' => '30 ml',
                        'gtin' => null,
                        'free_delivery' => false,
                        'amount' => 19.15,
                        'available' => true,
                        'url_path' => '/foo/baz/p-16056389/',
                    ],
                ],
            ),
            new ScraperResult(
                '/foo/qux',
                existingPageProductIds: [123, 456],
                scrapedBrand: ['name' => 'Valentino'],
                scrapedFragrance: [
                    'name' => 'Uomo Intense',
                    'gender' => FragranceGender::MALE,
                    'type' => FragranceType::EAU_DE_PARFUM,
                ],
                scrapedVariants: [
                    [
                        'name' => '50 ml',
                        'gtin' => null,
                        'free_delivery' => false,
                        'amount' => 47.50,
                        'available' => true,
                        'url_path' => '/foo/qux/p-601941/',
                    ],
                ],
            ),
            new ScraperResult('/foo/quux', isRelevant: false),
        ];

        $page1 = new MerchantPageModel();
        $moschino = new BrandModel();
        $toyBoy = new FragranceModel();
        $tb100 = new VariantModel();
        $tb50 = new VariantModel();
        $tb30 = new VariantModel();
        $tb100p = new ProductModel();
        $tb50p = new ProductModel();
        $tb30p = new ProductModel();
        $page2 = new MerchantPageModel();
        $valentino = new BrandModel();
        $uomo = new FragranceModel();
        $uomo50 = new VariantModel();
        $uomo50p = new ProductModel();
        $uomo50p->setId(456);
        $missingProduct = new ProductModel();

        $this->createConnectionBeginTransactionExpectation();
        $this->createMerchantPageLazyCreationExpectation(
            [$merchant, '/foo/baz', ['failed_scrape_days' => 0, 'should_scrape' => true,]],
            $page1
        );
        $this->createBrandLazyCreationExpectation(['Moschino'], $moschino);
        $this->createFragranceLazyCreationExpectation(
            [$moschino, 'Toy Boy', FragranceGender::MALE, FragranceType::EAU_DE_PARFUM],
            $toyBoy
        );
        $this->createVariantLazyCreationExpectation([$toyBoy, '100 ml', null], $tb100);
        $this->createVariantLazyCreationExpectation([$toyBoy, '50 ml', null], $tb50);
        $this->createVariantLazyCreationExpectation([$toyBoy, '30 ml', null], $tb30);
        $this->createProductLazyCreationExpectation(
            [$merchant, $tb100, $page1, '/foo/baz/p-16007859/', ['free_delivery' => false]],
            $tb100p
        );
        $this->createPriceManagerExpectation([$tb100p, null, false]);
        $this->createProductLazyCreationExpectation(
            [$merchant, $tb50, $page1, '/foo/baz/p-16006251/', ['free_delivery' => false]],
            $tb50p
        );
        $this->createPriceManagerExpectation([$tb50p, null, false]);
        $this->createProductLazyCreationExpectation(
            [$merchant, $tb30, $page1, '/foo/baz/p-16056389/', ['free_delivery' => false]],
            $tb30p
        );
        $this->createPriceManagerExpectation([$tb30p, 19.15, true]);
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createMerchantPageLazyCreationExpectation(
            [$merchant, '/foo/qux', ['failed_scrape_days' => 0, 'should_scrape' => true,]],
            $page2
        );
        $this->createBrandLazyCreationExpectation(['Valentino'], $valentino);
        $this->createFragranceLazyCreationExpectation(
            [$valentino, 'Uomo Intense', FragranceGender::MALE, FragranceType::EAU_DE_PARFUM],
            $uomo
        );
        $this->createVariantLazyCreationExpectation([$uomo, '50 ml', null], $uomo50);
        $this->createProductLazyCreationExpectation(
            [$merchant, $uomo50, $page2, '/foo/qux/p-601941/', ['free_delivery' => false]],
            $uomo50p
        );
        $this->createPriceManagerExpectation([$uomo50p, 47.50, true]);
        $this->createProductRepositoryExpectation([123], ['id' => 123]);
        $this->createPriceManagerExpectation([m::type(ProductModel::class), null, false]);
        $this->createConnectionCommitExpectation();

        $this->createUrlIgnoreListExpectation([$merchant, ['/foo/bar', '/foo/quux']]);

        $this->scraperResultsProcessor->process($merchant, $results);
    }

    public function testProcessUsesFragranceOfExistingVariants(): void
    {
        $merchant = new MerchantModel();
        $gtin = '3274872368026';
        $results = [
            new ScraperResult(
                '/foo/bar',
                scrapedBrand: ['name' => 'Givenchy'],
                scrapedFragrance: [
                    'name' => 'Gentleman',
                    'gender' => FragranceGender::MALE,
                    'type' => FragranceType::EAU_DE_PARFUM,
                ],
                scrapedVariants: [
                    [
                        'name' => '100 ml',
                        'gtin' => $gtin,
                        'free_delivery' => false,
                        'amount' => 62.95,
                        'available' => true,
                        'url_path' => '/foo/bar',
                    ],
                ],
            ),
        ];

        $page = new MerchantPageModel();
        $fragranceId = 123;
        $fragranceResult = ['id' => $fragranceId];
        $gentleman100 = new VariantModel();
        $gentleman100Product = new ProductModel();

        $this->createConnectionBeginTransactionExpectation();
        $this->createMerchantPageLazyCreationExpectation(
            [$merchant, '/foo/bar', ['failed_scrape_days' => 0, 'should_scrape' => true,]],
            $page
        );
        $this->createVariantRepositoryExpectation([[$gtin]], $fragranceId);
        $this->createFragranceRepositoryExpectation([$fragranceId], $fragranceResult);
        $this->createVariantLazyCreationExpectation([m::type(FragranceModel::class), '100 ml', $gtin], $gentleman100);
        $this->createProductLazyCreationExpectation(
            [$merchant, $gentleman100, $page, '/foo/bar', ['free_delivery' => false]],
            $gentleman100Product
        );
        $this->createPriceManagerExpectation([$gentleman100Product, 62.95, true]);
        $this->createConnectionCommitExpectation();

        $this->scraperResultsProcessor->process($merchant, $results);
    }

    public function testProcessUsesFragranceLazyCreationIfVariantsNotFoundWithGtin(): void
    {
        $merchant = new MerchantModel();
        $gtin = '3274872368026';
        $results = [
            new ScraperResult(
                '/foo/bar',
                scrapedBrand: ['name' => 'Givenchy'],
                scrapedFragrance: [
                    'name' => 'Gentleman',
                    'gender' => FragranceGender::MALE,
                    'type' => FragranceType::EAU_DE_PARFUM,
                ],
                scrapedVariants: [
                    [
                        'name' => '100 ml',
                        'gtin' => $gtin,
                        'free_delivery' => false,
                        'amount' => 62.95,
                        'available' => true,
                        'url_path' => '/foo/bar',
                    ],
                ],
            ),
        ];

        $page = new MerchantPageModel();
        $givenchy = new BrandModel();
        $gentleman = new FragranceModel();
        $gentleman100 = new VariantModel();
        $gentleman100Product = new ProductModel();

        $this->createConnectionBeginTransactionExpectation();
        $this->createMerchantPageLazyCreationExpectation(
            [$merchant, '/foo/bar', ['failed_scrape_days' => 0, 'should_scrape' => true,]],
            $page
        );
        $this->createVariantRepositoryExpectation([[$gtin]], null);
        $this->createBrandLazyCreationExpectation(['Givenchy'], $givenchy);
        $this->createFragranceLazyCreationExpectation(
            [$givenchy, 'Gentleman', FragranceGender::MALE, FragranceType::EAU_DE_PARFUM],
            $gentleman
        );
        $this->createVariantLazyCreationExpectation([$gentleman, '100 ml', $gtin], $gentleman100);
        $this->createProductLazyCreationExpectation(
            [$merchant, $gentleman100, $page, '/foo/bar', ['free_delivery' => false]],
            $gentleman100Product
        );
        $this->createPriceManagerExpectation([$gentleman100Product, 62.95, true]);
        $this->createConnectionCommitExpectation();

        $this->scraperResultsProcessor->process($merchant, $results);
    }

    private function createConnectionBeginTransactionExpectation(): void
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createMerchantPageLazyCreationExpectation(array $args, MerchantPageModel $result): void
    {
        $this->merchantPageLazyCreation
            ->shouldReceive('createOrUpdate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVariantRepositoryExpectation(array $args, ?int $result): void
    {
        $this->variantRepository
            ->shouldReceive('getFragranceIdForGtins')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFragranceRepositoryExpectation(array $args, ?array $result): void
    {
        $this->fragranceRepository
            ->shouldReceive('findOneById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createBrandLazyCreationExpectation(array $args, BrandModel $result): void
    {
        $this->brandLazyCreation
            ->shouldReceive('createOrRetrieve')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFragranceLazyCreationExpectation(array $args, FragranceModel $result): void
    {
        $this->fragranceLazyCreation
            ->shouldReceive('createOrRetrieve')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVariantLazyCreationExpectation(array $args, VariantModel $result): void
    {
        $this->variantLazyCreation
            ->shouldReceive('createOrRetrieve')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createProductLazyCreationExpectation(array $args, ProductModel $result): void
    {
        $this->productLazyCreation
            ->shouldReceive('createOrUpdate')
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

    private function createConnectionCommitExpectation(): void
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createUrlIgnoreListExpectation(array $args): void
    {
        $this->urlIgnoreList
            ->shouldReceive('add')
            ->once()
            ->with(...$args);
    }
}
