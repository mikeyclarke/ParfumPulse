<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Price;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Price\PriceCreator;
use ParfumPulse\Price\PriceStorage;
use ParfumPulse\Product\ProductModel;

class PriceCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $priceStorage;
    private PriceCreator $priceCreator;

    public function setUp(): void
    {
        $this->priceStorage = m::mock(PriceStorage::class);

        $this->priceCreator = new PriceCreator(
            $this->priceStorage,
        );
    }

    public function testCreate(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 27.16;

        $this->createPriceStorageExpectation(
            [$productId, $amount, m::pattern('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/')]
        );

        $this->priceCreator->create($product, $amount);
    }

    private function createPriceStorageExpectation(array $args): void
    {
        $this->priceStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }
}
