<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Merchant;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Merchant\MerchantCreator;
use ParfumPulse\Merchant\MerchantLazyCreation;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Merchant\MerchantRepository;

class MerchantLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantCreator;
    private LegacyMockInterface $merchantRepository;
    private MerchantLazyCreation $merchantLazyCreation;

    public function setUp(): void
    {
        $this->merchantCreator = m::mock(MerchantCreator::class);
        $this->merchantRepository = m::mock(MerchantRepository::class);

        $this->merchantLazyCreation = new MerchantLazyCreation(
            $this->merchantCreator,
            $this->merchantRepository,
        );
    }

    public function testCreateOrRetrieveWhenMerchantAlreadyExists(): void
    {
        $code = 'notino';

        $merchantRow = [
            'id' => 123,
            'code' => $code,
        ];

        $this->createMerchantRepositoryExpectation([$code], $merchantRow);

        $result = $this->merchantLazyCreation->createOrRetrieve($code);
        $this->assertInstanceOf(MerchantModel::class, $result);
    }

    public function testCreateOrRetrieveWhenMerchantDoesNotYetExist(): void
    {
        $code = 'notino';

        $merchant = new MerchantModel();

        $this->createMerchantRepositoryExpectation([$code], null);
        $this->createMerchantCreatorExpectation([$code], $merchant);

        $result = $this->merchantLazyCreation->createOrRetrieve($code);
        $this->assertEquals($merchant, $result);
    }

    private function createMerchantRepositoryExpectation(array $args, ?array $result): void
    {
        $this->merchantRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMerchantCreatorExpectation(array $args, MerchantModel $result): void
    {
        $this->merchantCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
