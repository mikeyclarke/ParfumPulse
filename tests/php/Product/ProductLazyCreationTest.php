<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Product;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Product\ProductCreator;
use ParfumPulse\Product\ProductLazyCreation;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductRepository;
use ParfumPulse\Product\ProductUpdater;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Variant\VariantModel;

class ProductLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $productCreator;
    private LegacyMockInterface $productRepository;
    private LegacyMockInterface $productUpdater;
    private ProductLazyCreation $productLazyCreation;

    public function setUp(): void
    {
        $this->productCreator = m::mock(ProductCreator::class);
        $this->productRepository = m::mock(ProductRepository::class);
        $this->productUpdater = m::mock(ProductUpdater::class);

        $this->productLazyCreation = new ProductLazyCreation(
            $this->productCreator,
            $this->productRepository,
            $this->productUpdater,
        );
    }

    public function testCreateOrUpdateWhenProductAlreadyExists(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $variantId = 456;
        $variant = VariantModel::createFromArray(['id' => $variantId]);
        $merchantPageId = 789;
        $merchantPage = MerchantPageModel::createFromArray(['id' => $merchantPageId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456';
        $parameters = [
            'free_delivery' => true,
        ];

        $productRow = [
            'free_delivery' => false,
            'variant_id' => $variantId,
            'merchant_page_id' => $merchantPageId,
            'merchant_id' => $merchantId,
        ];
        $product = new ProductModel();

        $this->createProductRepositoryExpectation(
            [['merchant_id' => $merchantId, 'variant_id' => $variantId]],
            $productRow
        );
        $this->createProductUpdaterExpectation(
            [m::type(ProductModel::class), ['free_delivery' => $parameters['free_delivery']]],
            $product
        );

        $result = $this->productLazyCreation->createOrUpdate($merchant, $variant, $merchantPage, $urlPath, $parameters);
        $this->assertEquals($product, $result);
    }

    public function testCreateOrUpdateWhenProductDoesNotYetExist(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $variantId = 456;
        $variant = VariantModel::createFromArray(['id' => $variantId]);
        $merchantPageId = 789;
        $merchantPage = MerchantPageModel::createFromArray(['id' => $merchantPageId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456';
        $parameters = [
            'free_delivery' => false,
        ];

        $product = new ProductModel();

        $this->createProductRepositoryExpectation([['merchant_id' => $merchantId, 'variant_id' => $variantId]], null);
        $this->createProductCreatorExpectation(
            [$merchant, $variant, $merchantPage, array_merge($parameters, ['url_path' => $urlPath])],
            $product
        );

        $result = $this->productLazyCreation->createOrUpdate($merchant, $variant, $merchantPage, $urlPath, $parameters);
        $this->assertEquals($product, $result);
    }

    private function createProductRepositoryExpectation(array $args, ?array $result): void
    {
        $this->productRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createProductCreatorExpectation(array $args, ProductModel $result): void
    {
        $this->productCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createProductUpdaterExpectation(array $args, ProductModel $result): void
    {
        $this->productUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
