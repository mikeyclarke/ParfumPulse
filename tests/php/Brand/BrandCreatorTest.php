<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Brand;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandStorage;
use ParfumPulse\Brand\BrandValidator;
use ParfumPulse\Url\UrlSlugGenerator;

class BrandCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandStorage;
    private LegacyMockInterface $brandValidator;
    private LegacyMockInterface $urlSlugGenerator;
    private BrandCreator $brandCreator;

    public function setUp(): void
    {
        $this->brandStorage = m::mock(BrandStorage::class);
        $this->brandValidator = m::mock(BrandValidator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->brandCreator = new BrandCreator(
            $this->brandStorage,
            $this->brandValidator,
            $this->urlSlugGenerator,
        );
    }

    public function testCreate(): void
    {
        $name = 'Chanel';

        $urlSlug = 'chanel';
        $brandRow = [
            'name' => $name,
            'url_slug' => $urlSlug,
        ];

        $this->createBrandValidatorExpectation([['name' => $name], true]);
        $this->createUrlSlugGeneratorExpectation([$name], $urlSlug);
        $this->createBrandStorageExpectation([$name, $urlSlug], $brandRow);

        $result = $this->brandCreator->create($name);
        $this->assertInstanceOf(BrandModel::class, $result);
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($urlSlug, $result->getUrlSlug());
    }

    private function createBrandValidatorExpectation(array $args): void
    {
        $this->brandValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createUrlSlugGeneratorExpectation(array $args, string $result): void
    {
        $this->urlSlugGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createBrandStorageExpectation(array $args, array $result): void
    {
        $this->brandStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
