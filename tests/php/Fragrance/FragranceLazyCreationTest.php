<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Fragrance;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceCreator;
use ParfumPulse\Fragrance\FragranceLazyCreation;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceRepository;
use ParfumPulse\Typography\StringNormalizer;

class FragranceLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $fragranceCreator;
    private LegacyMockInterface $fragranceRepository;
    private LegacyMockInterface $stringNormalizer;
    private FragranceLazyCreation $fragranceLazyCreation;

    public function setUp(): void
    {
        $this->fragranceCreator = m::mock(FragranceCreator::class);
        $this->fragranceRepository = m::mock(FragranceRepository::class);
        $this->stringNormalizer = m::mock(StringNormalizer::class);

        $this->fragranceLazyCreation = new FragranceLazyCreation(
            $this->fragranceCreator,
            $this->fragranceRepository,
            $this->stringNormalizer,
        );
    }

    public function testCreateOrRetrieveWhenFragranceAlreadyExists(): void
    {
        $brandId = 123;
        $brand = BrandModel::createFromArray(['id' => $brandId]);
        $name = 'Terre d\'Hermès';
        $gender = 'male';
        $type = 'eau de parfum';

        $normalized = 'Terre d’Hermès';
        $fragranceRow = [
            'id' => 456,
            'name' => $normalized,
            'gender' => $gender,
            'type' => $type,
            'brand_id' => $brandId,
        ];

        $this->createStringNormalizerExpectation([$name], $normalized);
        $this->createFragranceRepositoryExpectation(
            [['brand_id' => $brandId, 'name' => $normalized, 'gender' => $gender, 'type' => $type]],
            $fragranceRow
        );

        $result = $this->fragranceLazyCreation->createOrRetrieve($brand, $name, $gender, $type);
        $this->assertInstanceOf(FragranceModel::class, $result);
    }

    public function testCreateOrRetrieveWhenFragranceDoesNotYetExist(): void
    {
        $brandId = 123;
        $brand = BrandModel::createFromArray(['id' => $brandId]);
        $name = 'Terre d\'Hermès';
        $gender = 'male';
        $type = 'eau de parfum';

        $normalized = 'Terre d’Hermès';
        $fragrance = new FragranceModel();

        $this->createStringNormalizerExpectation([$name], $normalized);
        $this->createFragranceRepositoryExpectation(
            [['brand_id' => $brandId, 'name' => $normalized, 'gender' => $gender, 'type' => $type]],
            null
        );
        $this->createFragranceCreatorExpectation(
            [$brand, ['name' => $normalized, 'gender' => $gender, 'type' => $type]],
            $fragrance
        );

        $result = $this->fragranceLazyCreation->createOrRetrieve($brand, $name, $gender, $type);
        $this->assertInstanceOf(FragranceModel::class, $result);
    }

    private function createStringNormalizerExpectation(array $args, string $result): void
    {
        $this->stringNormalizer
            ->shouldReceive('normalize')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFragranceRepositoryExpectation(array $args, ?array $result): void
    {
        $this->fragranceRepository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFragranceCreatorExpectation(array $args, FragranceModel $result): void
    {
        $this->fragranceCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
