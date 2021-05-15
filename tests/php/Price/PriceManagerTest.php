<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Price;

use DateTime;
use ParfumPulse\Price\PriceCreator;
use ParfumPulse\Price\PriceManager;
use ParfumPulse\Price\PriceModel;
use ParfumPulse\Price\PriceRepository;
use ParfumPulse\Price\PriceStorage;
use ParfumPulse\Product\ProductModel;
use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;

class PriceManagerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $priceCreator;
    private LegacyMockInterface $priceRepository;
    private LegacyMockInterface $priceStorage;
    private PriceManager $priceManager;

    public function setUp(): void
    {
        $this->priceCreator = m::mock(PriceCreator::class);
        $this->priceRepository = m::mock(PriceRepository::class);
        $this->priceStorage = m::mock(PriceStorage::class);

        $this->priceManager = new PriceManager(
            $this->priceCreator,
            $this->priceRepository,
            $this->priceStorage,
        );
    }

    public function testProductWithNoCurrentPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 67.80;
        $isAvailable = true;

        $this->createPriceRepositoryExpectation([$productId], null);
        $this->createPriceCreatorExpectation([$product, $amount]);

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testUnavailableProductWithNoCurrentPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 67.80;
        $isAvailable = false;

        $this->createPriceRepositoryExpectation([$productId], null);

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testProductWithNullAmountAndNoCurrentPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = null;
        $isAvailable = true;

        $this->createPriceRepositoryExpectation([$productId], null);

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testProductWithUnchangedPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 67.80;
        $isAvailable = true;

        $currentPriceArray = [
            'id' => 456,
            'amount' => $amount,
        ];

        $this->createPriceRepositoryExpectation([$productId], $currentPriceArray);

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testUnavailableProductWithCurrentPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 67.80;
        $isAvailable = false;

        $priceId = 456;
        $currentPriceArray = [
            'id' => $priceId,
            'amount' => 55.67,
        ];

        $this->createPriceRepositoryExpectation([$productId], $currentPriceArray);
        $this->createPriceStorageExpectation(
            [$priceId, m::on(function ($arg) {
                return is_array($arg) &&
                    isset($arg['time_to']) &&
                    preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $arg['time_to']);
            })]
        );

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testProductWithNullAmountAndCurrentPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = null;
        $isAvailable = true;

        $priceId = 456;
        $currentPriceArray = [
            'id' => $priceId,
            'amount' => 55.67,
        ];

        $this->createPriceRepositoryExpectation([$productId], $currentPriceArray);
        $this->createPriceStorageExpectation(
            [$priceId, m::on(function ($arg) {
                return is_array($arg) &&
                    isset($arg['time_to']) &&
                    preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $arg['time_to']);
            })]
        );

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    public function testProductWithChangedPrice(): void
    {
        $productId = 123;
        $product = ProductModel::createFromArray(['id' => $productId]);
        $amount = 42.20;
        $isAvailable = true;

        $priceId = 456;
        $currentPriceArray = [
            'id' => $priceId,
            'amount' => 55.67,
        ];

        $this->createPriceRepositoryExpectation([$productId], $currentPriceArray);
        $this->createPriceStorageExpectation(
            [$priceId, m::on(function ($arg) {
                return is_array($arg) &&
                    isset($arg['time_to']) &&
                    preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $arg['time_to']);
            })]
        );
        $this->createPriceCreatorExpectation([$product, $amount]);

        $this->priceManager->registerPrice($product, $amount, $isAvailable);
    }

    private function createPriceRepositoryExpectation(array $args, ?array $result): void
    {
        $this->priceRepository
            ->shouldReceive('findCurrentPrice')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPriceCreatorExpectation(array $args): void
    {
        $this->priceCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args);
    }

    private function createPriceStorageExpectation(array $args): void
    {
        $this->priceStorage
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }
}
