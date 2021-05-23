<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Brand;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandLazyCreation;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandNameNormalizer;
use ParfumPulse\Brand\BrandRepository;

class BrandLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandCreator;
    private LegacyMockInterface $brandNameNormalizer;
    private LegacyMockInterface $brandRepository;
    private BrandLazyCreation $brandLazyCreation;

    public function setUp(): void
    {
        $this->brandCreator = m::mock(BrandCreator::class);
        $this->brandNameNormalizer = m::mock(BrandNameNormalizer::class);
        $this->brandRepository = m::mock(BrandRepository::class);

        $this->brandLazyCreation = new BrandLazyCreation(
            $this->brandCreator,
            $this->brandNameNormalizer,
            $this->brandRepository,
        );
    }

    public function testCreateOrRetrieveWhenBrandAlreadyExists(): void
    {
        $name = 'L\'Occitane';

        $normalized = 'L’Occitane';
        $brandRow = [
            'id' => 123,
            'name' => $normalized,
        ];

        $this->createBrandNameNormalizerExpectation([$name], $normalized);
        $this->createBrandRepositoryExpectation([$normalized], $brandRow);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertInstanceOf(BrandModel::class, $result);
    }

    public function testCreateOrRetrieveWhenBrandDoesNotYetExist(): void
    {
        $name = 'L\'Occitane';

        $normalized = 'L’Occitane';
        $brand = new BrandModel();

        $this->createBrandNameNormalizerExpectation([$name], $normalized);
        $this->createBrandRepositoryExpectation([$normalized], null);
        $this->createBrandCreatorExpectation([$normalized], $brand);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertEquals($brand, $result);
    }

    private function createBrandNameNormalizerExpectation(array $args, string $result): void
    {
        $this->brandNameNormalizer
            ->shouldReceive('normalize')
            ->once()
            ->with(...$args)
            ->andReturn($result);
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
