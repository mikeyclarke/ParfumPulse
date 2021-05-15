<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Variant;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Variant\VariantCreator;
use ParfumPulse\Variant\VariantLazyCreation;
use ParfumPulse\Variant\VariantModel;
use ParfumPulse\Variant\VariantRepository;

class VariantLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $variantCreator;
    private LegacyMockInterface $variantRepository;
    private VariantLazyCreation $variantLazyCreation;

    public function setUp(): void
    {
        $this->variantCreator = m::mock(VariantCreator::class);
        $this->variantRepository = m::mock(VariantRepository::class);

        $this->variantLazyCreation = new VariantLazyCreation(
            $this->variantCreator,
            $this->variantRepository,
        );
    }

    public function testCreateOrRetrieveWhenVariantAlreadyExistsAndGtinProvided(): void
    {
        $fragranceId = 123;
        $fragrance = FragranceModel::createFromArray(['id' => $fragranceId]);
        $name = '100 ml';
        $gtin = '8011003845132';

        $variantRow = [
            'id' => 456,
            'name' => $name,
            'gtin' => $gtin,
            'fragrance_id' => $fragranceId,
        ];

        $this->createVariantRepositoryFindOneByGtinOrNameExpectation([$gtin, $name, $fragranceId], $variantRow);

        $result = $this->variantLazyCreation->createOrRetrieve($fragrance, $name, $gtin);
        $this->assertInstanceOf(VariantModel::class, $result);
    }

    public function testCreateOrRetrieveWhenVariantAlreadyExistsAndGtinNotProvided(): void
    {
        $fragranceId = 123;
        $fragrance = FragranceModel::createFromArray(['id' => $fragranceId]);
        $name = '100 ml';

        $variantRow = [
            'id' => 456,
            'name' => $name,
            'gtin' => null,
            'fragrance_id' => $fragranceId,
        ];

        $this->createVariantRepositoryFindOneByNameExpectation([$name, $fragranceId], $variantRow);

        $result = $this->variantLazyCreation->createOrRetrieve($fragrance, $name);
        $this->assertInstanceOf(VariantModel::class, $result);
    }

    public function testCreateOrRetrieveWhenVariantDoesNotYetExistAndGtinProvided(): void
    {
        $fragranceId = 123;
        $fragrance = FragranceModel::createFromArray(['id' => $fragranceId]);
        $name = '100 ml';
        $gtin = '8011003845132';

        $variant = new VariantModel();

        $this->createVariantRepositoryFindOneByGtinOrNameExpectation([$gtin, $name, $fragranceId], null);
        $this->createVariantCreatorExpectation([$fragrance, ['name' => $name, 'gtin' => $gtin]], $variant);

        $result = $this->variantLazyCreation->createOrRetrieve($fragrance, $name, $gtin);
        $this->assertEquals($variant, $result);
    }

    public function testCreateOrRetrieveWhenVariantDoesNotYetExistAndGtinNotProvided(): void
    {
        $fragranceId = 123;
        $fragrance = FragranceModel::createFromArray(['id' => $fragranceId]);
        $name = '100 ml';

        $variant = new VariantModel();

        $this->createVariantRepositoryFindOneByNameExpectation([$name, $fragranceId], null);
        $this->createVariantCreatorExpectation([$fragrance, ['name' => $name, 'gtin' => null]], $variant);

        $result = $this->variantLazyCreation->createOrRetrieve($fragrance, $name);
        $this->assertEquals($variant, $result);
    }

    private function createVariantRepositoryFindOneByNameExpectation(array $args, ?array $result): void
    {
        $this->variantRepository
            ->shouldReceive('findOneByName')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVariantRepositoryFindOneByGtinOrNameExpectation(array $args, ?array $result): void
    {
        $this->variantRepository
            ->shouldReceive('findOneByGtinOrName')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVariantCreatorExpectation(array $args, VariantModel $result): void
    {
        $this->variantCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
