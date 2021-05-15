<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Merchant;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Merchant\Exception\NoMerchantWithCodeException;
use ParfumPulse\Merchant\MerchantCreator;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Merchant\MerchantStorage;

class MerchantCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantStorage;
    private array $merchantsConfig;
    private MerchantCreator $merchantCreator;

    public function setUp(): void
    {
        $this->merchantStorage = m::mock(MerchantStorage::class);
        $this->merchantsConfig = [
            'notino' => [],
        ];

        $this->merchantCreator = new MerchantCreator(
            $this->merchantStorage,
            $this->merchantsConfig,
        );
    }

    public function testCreate(): void
    {
        $code = 'notino';

        $merchantRow = [
            'id' => 123,
            'code' => $code,
        ];

        $this->createMerchantStorageExpectation([$code], $merchantRow);

        $result = $this->merchantCreator->create($code);
        $this->assertInstanceOf(MerchantModel::class, $result);
    }

    public function testCreateWithInvalidMerchantCode(): void
    {
        $code = 'foo';

        $this->expectException(NoMerchantWithCodeException::class);

        $this->merchantCreator->create($code);
    }

    private function createMerchantStorageExpectation(array $args, array $result): void
    {
        $this->merchantStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
