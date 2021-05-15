<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Product;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\Product\ProductCreator;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductStorage;
use ParfumPulse\Product\ProductValidator;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Variant\VariantModel;

class ProductCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $productStorage;
    private LegacyMockInterface $productValidator;
    private ProductCreator $productCreator;

    public function setUp(): void
    {
        $this->productStorage = m::mock(ProductStorage::class);
        $this->productValidator = m::mock(ProductValidator::class);

        $this->productCreator = new ProductCreator(
            $this->productStorage,
            $this->productValidator,
        );
    }

    public function testCreate(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $variantId = 456;
        $variant = VariantModel::createFromArray(['id' => $variantId]);
        $merchantPageId = 789;
        $merchantPage = MerchantPageModel::createFromArray(['id' => $merchantPageId]);
        $parameters = [
            'url_path' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456',
        ];

        $productRow = array_merge(
            $parameters,
            [
                'free_delivery' => false,
                'variant_id' => $variantId,
                'merchant_page_id' => $merchantPageId,
                'merchant_id' => $merchantId,
            ]
        );

        $this->createProductValidatorExpectation([$parameters, true]);
        $this->createProductStorageExpectation(
            [$parameters['url_path'], $variantId, $merchantPageId, $merchantId, false],
            $productRow
        );

        $result = $this->productCreator->create($merchant, $variant, $merchantPage, $parameters);
        $this->assertInstanceOf(ProductModel::class, $result);
        $this->assertEquals($parameters['url_path'], $result->getUrlPath());
        $this->assertFalse($result->hasFreeDelivery());
        $this->assertEquals($variantId, $result->getVariantId());
        $this->assertEquals($merchantPageId, $result->getMerchantPageId());
        $this->assertEquals($merchantId, $result->getMerchantId());
    }

    public function testCreateWithFreeDelivery(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $variantId = 456;
        $variant = VariantModel::createFromArray(['id' => $variantId]);
        $merchantPageId = 789;
        $merchantPage = MerchantPageModel::createFromArray(['id' => $merchantPageId]);
        $parameters = [
            'url_path' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456',
            'free_delivery' => true,
        ];

        $productRow = array_merge(
            $parameters,
            [
                'variant_id' => $variantId,
                'merchant_page_id' => $merchantPageId,
                'merchant_id' => $merchantId,
            ]
        );

        $this->createProductValidatorExpectation([$parameters, true]);
        $this->createProductStorageExpectation(
            [$parameters['url_path'], $variantId, $merchantPageId, $merchantId, true],
            $productRow
        );

        $result = $this->productCreator->create($merchant, $variant, $merchantPage, $parameters);
        $this->assertInstanceOf(ProductModel::class, $result);
        $this->assertEquals($parameters['url_path'], $result->getUrlPath());
        $this->assertTrue($result->hasFreeDelivery());
        $this->assertEquals($variantId, $result->getVariantId());
        $this->assertEquals($merchantPageId, $result->getMerchantPageId());
        $this->assertEquals($merchantId, $result->getMerchantId());
    }

    private function createProductValidatorExpectation(array $args): void
    {
        $this->productValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createProductStorageExpectation(array $args, array $result): void
    {
        $this->productStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
