<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Brand;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandNameNormalizer;
use ParfumPulse\Typography\StringNormalizer;

class BrandNameNormalizerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $stringNormalizer;
    private array $brandNameAliases;
    private BrandNameNormalizer $brandNameNormalizer;

    public function setUp(): void
    {
        $this->stringNormalizer = m::mock(StringNormalizer::class);
        $this->brandNameAliases = [
            'United Colors of Benetton' => 'Benetton',
            'Viktor & Rolf' => 'Viktor&Rolf',
        ];

        $this->brandNameNormalizer = new BrandNameNormalizer(
            $this->stringNormalizer,
            $this->brandNameAliases,
        );
    }

    public function testNormalize(): void
    {
        $name = 'Etat Libre d\'Orange';

        $normalized = 'Etat Libre d’Orange';

        $this->createStringNormalizerExpectation([$name], $normalized);

        $result = $this->brandNameNormalizer->normalize($name);
        $this->assertEquals($normalized, $result);
    }

    public function testNormalizeReplacesBrandAlias(): void
    {
        $name = 'United Colors of Benetton';

        $this->createStringNormalizerExpectation([$name], $name);

        $expected = 'Benetton';

        $result = $this->brandNameNormalizer->normalize($name);
        $this->assertEquals($expected, $result);
    }

    private function createStringNormalizerExpectation(array $args, string $result): void
    {
        $this->stringNormalizer
            ->shouldReceive('normalize')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
