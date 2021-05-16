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
use ParfumPulse\Typography\StringNormalizer;

class BrandLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandCreator;
    private LegacyMockInterface $brandRepository;
    private LegacyMockInterface $stringNormalizer;
    private BrandLazyCreation $brandLazyCreation;

    public function setUp(): void
    {
        $this->brandCreator = m::mock(BrandCreator::class);
        $this->brandRepository = m::mock(BrandRepository::class);
        $this->stringNormalizer = m::mock(StringNormalizer::class);

        $this->brandLazyCreation = new BrandLazyCreation(
            $this->brandCreator,
            $this->brandRepository,
            $this->stringNormalizer,
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

        $this->createStringNormalizerExpectation([$name], $normalized);
        $this->createBrandRepositoryExpectation([$normalized], $brandRow);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertInstanceOf(BrandModel::class, $result);
    }

    public function testCreateOrRetrieveWhenBrandDoesNotYetExist(): void
    {
        $name = 'L\'Occitane';

        $normalized = 'L’Occitane';
        $brand = new BrandModel();

        $this->createStringNormalizerExpectation([$name], $normalized);
        $this->createBrandRepositoryExpectation([$normalized], null);
        $this->createBrandCreatorExpectation([$normalized], $brand);

        $result = $this->brandLazyCreation->createOrRetrieve($name);
        $this->assertEquals($brand, $result);
    }

    private function createStringNormalizerExpectation(array $args, string $result): void
    {
        $this->stringNormalizer
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
