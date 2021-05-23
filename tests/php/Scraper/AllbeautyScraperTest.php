<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Scraper;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Fragrance\FragranceGender;
use ParfumPulse\Fragrance\FragranceType;
use ParfumPulse\Scraper\AllbeautyScraper;
use ParfumPulse\Scraper\Exception\UnableToExtractDataException;
use ParfumPulse\Scraper\ScraperBot;
use ParfumPulse\Scraper\ScraperResult;

class AllbeautyScraperTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $bot;
    private AllbeautyScraper $allbeautyScraper;

    public function setUp(): void
    {
        $this->bot = m::mock(ScraperBot::class);

        $this->allbeautyScraper = new AllbeautyScraper();
        $this->allbeautyScraper->setBot($this->bot);
    }

    public function testScrape(): void
    {
        $url = '/foo/bar';
        $props = [
            'lastmod' => null,
            'productIds' => [],
            'hasFailedScrapeDays' => false,
        ];

        $productData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'audience' => [
                '@type' => 'Audience',
                'name' => 'Male',
            ],
            'brand' => [
                '@type' => 'Organization',
                'name' => 'Guerlain',
            ],
            'gtin13' => '3346470134911',
            'model' => 'Eau de Parfum Spray 100ml / 3.3 fl.oz.',
            'name' => 'Guerlain L\'Homme Ideal L\'Intense Eau de Parfum Spray 100ml / 3.3 fl.oz.',
            'offers' => [
                [
                    '@type' => 'Offer',
                    'price' => 99.35,
                    'priceCurrency' => 'AUD',
                ],
                [
                    '@type' => 'Offer',
                    'price' => 53.95,
                    'priceCurrency' => 'GBP',
                ],
                [
                    '@type' => 'Offer',
                    'price' => 8500,
                    'priceCurrency' => 'JPY',
                ],
            ],
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation($productData);

        $expectedBrand = [
            'name' => 'Guerlain',
        ];
        $expectedFragrance = [
            'name' => 'L\'Homme Ideal L\'Intense',
            'type' => FragranceType::EAU_DE_PARFUM,
            'gender' => FragranceGender::MALE,
        ];
        $expectedVariants = [
            [
                'gtin' => '3346470134911',
                'name' => '100 ml',
                'amount' => 53.95,
                'url_path' => $url,
                'available' => true,
                'free_delivery' => false,
            ],
        ];

        $result = $this->allbeautyScraper->scrape($url, $props);
        $this->assertInstanceOf(ScraperResult::class, $result);
        $this->assertEquals($url, $result->getPageUrlPath());
        $this->assertTrue($result->isRelevant());
        $this->assertEquals($expectedBrand, $result->getScrapedBrand());
        $this->assertEquals($expectedFragrance, $result->getScrapedFragrance());
        $this->assertEquals($expectedVariants, $result->getScrapedVariants());
    }

    public function testScrapeWithNullProductData(): void
    {
        $url = '/foo/bar';
        $props = [
            'lastmod' => null,
            'productIds' => [],
            'hasFailedScrapeDays' => false,
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation(null);

        $this->expectException(UnableToExtractDataException::class);

        $this->allbeautyScraper->scrape($url, $props);
    }

    public function testScrapeWithNonFragranceProduct(): void
    {
        $url = '/foo/bar';
        $props = [
            'lastmod' => null,
            'productIds' => [],
            'hasFailedScrapeDays' => false,
        ];

        $productData = [
            'model' => 'Treatment Fragrance Vitality Freshness Firmness Natural Splash 200ml / 6.7 fl.oz.',
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation($productData);

        $result = $this->allbeautyScraper->scrape($url, $props);
        $this->assertInstanceOf(ScraperResult::class, $result);
        $this->assertEquals($url, $result->getPageUrlPath());
        $this->assertFalse($result->isRelevant());
    }

    private function createBotCreateRequestExpectation(array $args): void
    {
        $this->bot
            ->shouldReceive('createRequest')
            ->once()
            ->with(...$args);
    }

    private function createBotGetStructuredProductDataExpectation(?array $result): void
    {
        $this->bot
            ->shouldReceive('getStructuredProductData')
            ->once()
            ->andReturn($result);
    }
}
