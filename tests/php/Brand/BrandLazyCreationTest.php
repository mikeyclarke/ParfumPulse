<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Brand;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandLazyCreation;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;

class BrandLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandCreator;
    private LegacyMockInterface $brandRepository;
    private BrandLazyCreation $brandLazyCreation;

    public function setUp(): void
    {
        $this->brandCreator = m::mock(BrandCreator::class);
        $this->brandRepository = m::mock(BrandRepository::class);

        $this->brandLazyCreation = new BrandLazyCreation(
            $this->brandCreator,
            $this->brandRepository,
        );
    }

    public function testCreateOrRetrieveWhenBrandAlreadyExists(): void
    {
        $name = 'Chanel';

        $brandRow = [
            'id' => 123,
            'name' => $name,
        ];

        $this->createBrandRepositoryExpectation([$name], $brandRow);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertInstanceOf(BrandModel::class, $result);
    }

    public function testCreateOrRetrieveWhenBrandDoesNotYetExist(): void
    {
        $name = 'Chanel';

        $brand = new BrandModel();

        $this->createBrandRepositoryExpectation([$name], null);
        $this->createBrandCreatorExpectation([$name], $brand);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertEquals($brand, $result);
    }

    private function createBrandRepositoryExpectation(array $args, ?array $result): void
    {
        $this->brandRepository
            ->shouldReceive('findOneByName')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createBrandCreatorExpectation(array $args, BrandModel $result): void
    {
        $this->brandCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
