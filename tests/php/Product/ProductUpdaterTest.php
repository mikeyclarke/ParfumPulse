<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Product;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Product\ProductUpdater;
use ParfumPulse\Product\ProductModel;
use ParfumPulse\Product\ProductStorage;
use ParfumPulse\Product\ProductValidator;

class ProductUpdaterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $productStorage;
    private LegacyMockInterface $productValidator;
    private ProductUpdater $productUpdater;

    public function setUp(): void
    {
        $this->productStorage = m::mock(ProductStorage::class);
        $this->productValidator = m::mock(ProductValidator::class);

        $this->productUpdater = new ProductUpdater(
            $this->productStorage,
            $this->productValidator,
        );
    }

    public function testUpdate(): void
    {
        $productId = 123;
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456/';
        $product = ProductModel::createFromArray([
            'id' => $productId,
            'url_path' => $urlPath,
            'free_delivery' => false,
        ]);
        $parameters = [
            'free_delivery' => true,
        ];

        $productRow = [
            'id' => $productId,
            'url_path' => $urlPath,
            'free_delivery' => true,
        ];

        $this->createProductValidatorExpectation([$parameters]);
        $this->createProductStorageExpectation([$productId, $parameters], $productRow);

        $result = $this->productUpdater->update($product, $parameters);
        $this->assertEquals($product, $result);
        $this->assertTrue($result->hasFreeDelivery());
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
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
