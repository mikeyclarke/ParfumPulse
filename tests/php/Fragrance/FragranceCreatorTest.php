<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Fragrance;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceCreator;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceStorage;
use ParfumPulse\Fragrance\FragranceValidator;
use ParfumPulse\Url\UrlIdGenerator;
use ParfumPulse\Url\UrlSlugGenerator;

class FragranceCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $fragranceStorage;
    private LegacyMockInterface $fragranceValidator;
    private LegacyMockInterface $urlIdGenerator;
    private LegacyMockInterface $urlSlugGenerator;
    private FragranceCreator $fragranceCreator;

    public function setUp(): void
    {
        $this->fragranceStorage = m::mock(FragranceStorage::class);
        $this->fragranceValidator = m::mock(FragranceValidator::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->fragranceCreator = new FragranceCreator(
            $this->fragranceStorage,
            $this->fragranceValidator,
            $this->urlIdGenerator,
            $this->urlSlugGenerator,
        );
    }

    public function testCreate(): void
    {
        $brandId = 123;
        $brand = BrandModel::createFromArray(['id' => $brandId]);
        $parameters = [
            'name' => 'Toy Boy',
            'gender' => 'male',
            'type' => 'eau de parfum',
        ];

        $urlId = 'abcd1234';
        $urlSlug = 'toy-boy';
        $fragranceRow = array_merge(
            $parameters,
            [
                'id' => 456,
                'url_id' => $urlId,
                'url_slug' => $urlSlug,
                'brand_id' => $brandId,
            ]
        );

        $this->createFragranceValidatorExpectation([$parameters, true]);
        $this->createUrlIdGeneratorExpectation($urlId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $urlSlug);
        $this->createFragranceStorageExpectation(
            [$parameters['name'], $parameters['gender'], $parameters['type'], $urlId, $urlSlug, $brandId],
            $fragranceRow
        );

        $result = $this->fragranceCreator->create($brand, $parameters);
        $this->assertInstanceOf(FragranceModel::class, $result);
        $this->assertEquals($parameters['name'], $result->getName());
        $this->assertEquals($parameters['gender'], $result->getGender());
        $this->assertEquals($parameters['type'], $result->getType());
        $this->assertEquals($urlId, $result->getUrlId());
        $this->assertEquals($urlSlug, $result->getUrlSlug());
        $this->assertEquals($brandId, $result->getBrandId());
    }

    private function createFragranceValidatorExpectation(array $args): void
    {
        $this->fragranceValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createUrlIdGeneratorExpectation(string $result): void
    {
        $this->urlIdGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createUrlSlugGeneratorExpectation(array $args, string $result): void
    {
        $this->urlSlugGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createFragranceStorageExpectation(array $args, array $result): void
    {
        $this->fragranceStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
