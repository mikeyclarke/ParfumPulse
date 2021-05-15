<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Variant;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Variant\VariantCreator;
use ParfumPulse\Variant\VariantModel;
use ParfumPulse\Variant\VariantStorage;
use ParfumPulse\Variant\VariantValidator;

class VariantCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $variantStorage;
    private LegacyMockInterface $variantValidator;
    private VariantCreator $variantCreator;

    public function setUp(): void
    {
        $this->variantStorage = m::mock(VariantStorage::class);
        $this->variantValidator = m::mock(VariantValidator::class);

        $this->variantCreator = new VariantCreator(
            $this->variantStorage,
            $this->variantValidator,
        );
    }

    public function testCreate(): void
    {
        $fragranceId = 123;
        $fragrance = FragranceModel::createFromArray(['id' => $fragranceId]);
        $parameters = [
            'name' => '100 ml',
            'gtin' => '8011003845132',
        ];

        $variantRow = array_merge(
            $parameters,
            [
                'id' => 456,
            ]
        );

        $this->createVariantValidatorExpectation([$parameters, true]);
        $this->createVariantStorageExpectation([$parameters['name'], $parameters['gtin'], $fragranceId], $variantRow);

        $result = $this->variantCreator->create($fragrance, $parameters);
        $this->assertInstanceOf(VariantModel::class, $result);
        $this->assertEquals($parameters['name'], $result->getName());
        $this->assertEquals($parameters['gtin'], $result->getgtin());
    }

    private function createVariantValidatorExpectation(array $args): void
    {
        $this->variantValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createVariantStorageExpectation(array $args, array $result): void
    {
        $this->variantStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
