<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Scraper;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Fragrance\FragranceGender;
use ParfumPulse\Fragrance\FragranceType;
use ParfumPulse\Scraper\Exception\UnableToExtractDataException;
use ParfumPulse\Scraper\NotinoScraper;
use ParfumPulse\Scraper\ScraperBot;
use ParfumPulse\Scraper\ScraperResult;

class NotinoScraperTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $bot;
    private NotinoScraper $notinoScraper;

    public function setUp(): void
    {
        $this->bot = m::mock(ScraperBot::class);

        $this->notinoScraper = new NotinoScraper();
        $this->notinoScraper->setBot($this->bot);
    }

    public function testScrape(): void
    {
        $url = '/foo/bar';
        $props = [
            'lastmod' => null,
            'productIds' => [123, 456],
            'hasFailedScrapeDays' => false,
        ];

        $productData = [
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Chanel',
            ],
            'category' => 'Eau de Parfum for Men',
            'name' => 'Chanel Bleu de Chanel',
        ];
        $apolloStateData = [
            '$Variant:15850807.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 50,
                'package' => '50',
            ],
            '$Variant:15850807.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 81.32,
            ],
            '$Variant:15851966.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 0,
                'package' => '3 x 20',
            ],
            '$Variant:15851966.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 80.91,
            ],
            '$Variant:476223.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 150,
                'package' => '150',
            ],
            '$Variant:476223.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 161.36,
            ],
            '$Variant:494137.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 50,
                'package' => '50',
            ],
            '$Variant:494137.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 90.36,
            ],
            '$Variant:494164.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 100,
                'package' => '100',
            ],
            '$Variant:494164.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 117,
            ],
            '$Variant:644463.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 0,
                'package' => '3 x 20',
            ],
            '$Variant:644463.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 76.75,
            ],
            '$Variant:644471.parameters' => [
                '__typename' => 'Parameters',
                'amount' => 0,
                'package' => '3 x 20',
            ],
            '$Variant:644471.price' => [
                '__typename' => 'Price',
                'currency' => 'GBP',
                'tax' => 20,
                'value' => 89.9,
            ],
            'Variant:15850807' => [
                'additionalInfo' => '50 ml',
                'attributes' => [
                    'json' => [
                        'Damage' => [
                            'descriptions' => ['Damaged packaging'],
                            'usedId' => 420040,
                            'volumeInPercent' => 100,
                        ],
                        'PackageSize' => [
                            'depth' => 33,
                            'height' => 100,
                            'width' => 71,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '2600004200406',
                'parameters' => [
                    'id' => '$Variant:15850807.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:15850807.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-15850807/',
            ],
            'Variant:15851966' => [
                'additionalInfo' => '3 x 20 ml',
                'attributes' => [
                    'json' => [
                        'Damage' => [
                            'descriptions' => ['Damaged packaging'],
                            'usedId' => 421157,
                            'volumeInPercent' => 100,
                        ],
                        'PackageSize' => [
                            'depth' => 30,
                            'height' => 105,
                            'width' => 89,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '2600004211570',
                'parameters' => [
                    'id' => '$Variant:15851966.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:15851966.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-15851966/',
            ],
            'Variant:476223' => [
                'additionalInfo' => '150 ml',
                'attributes' => [
                    'json' => [
                        'Master' => true,
                        'PackageSize' => [
                            'depth' => 45,
                            'height' => 130,
                            'width' => 95,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '3145891073706',
                'parameters' => [
                    'id' => '$Variant:476223.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:476223.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-476223/',
            ],
            'Variant:494137' => [
                'additionalInfo' => '50 ml',
                'attributes' => [
                    'json' => [
                        'PackageSize' => [
                            'depth' => 33,
                            'height' => 100,
                            'width' => 71,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '3145891073508',
                'parameters' => [
                    'id' => '$Variant:494137.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:494137.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-494137/',
            ],
            'Variant:494164' => [
                'additionalInfo' => '100 ml',
                'attributes' => [
                    'json' => [
                        'PackageSize' => [
                            'depth' => 37,
                            'height' => 122,
                            'width' => 90,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '3145891073607',
                'parameters' => [
                    'id' => '$Variant:494164.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:494164.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-494164/',
            ],
            'Variant:644463' => [
                'additionalInfo' => '3 x 20 ml',
                'attributes' => [
                    'json' => [
                        'PackageSize' => [
                            'depth' => 73,
                            'height' => 106,
                            'width' => 73,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '3145891073102',
                'parameters' => [
                    'id' => '$Variant:644463.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:644463.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-644463/',
            ],
            'Variant:644471' => [
                'additionalInfo' => '3 x 20 ml',
                'attributes' => [
                    'json' => [
                        'PackageSize' => [
                            'depth' => 30,
                            'height' => 105,
                            'width' => 89,
                        ],
                    ],
                ],
                'canBuy' => true,
                'eanCode' => '3145891073003',
                'parameters' => [
                    'id' => '$Variant:644471.parameters',
                    'type' => 'id',
                    'typename' => 'Parameters',
                ],
                'price' => [
                    'generated' => true,
                    'id' => '$Variant:644471.price',
                    'type' => 'id',
                    'typename' => 'Price',
                ],
                'url' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-644471/',
            ],
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation($productData);
        $this->createBotGetApolloStateDataExpectation($apolloStateData);

        $expectedBrand = [
            'name' => 'Chanel',
        ];
        $expectedFragrance = [
            'name' => 'Bleu de Chanel',
            'type' => FragranceType::EAU_DE_PARFUM,
            'gender' => FragranceGender::MALE,
        ];
        $expectedVariants = [
            [
                'gtin' => '3145891073706',
                'name' => '150 ml',
                'amount' => 161.36,
                'url_path' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-476223/',
                'available' => true,
                'free_delivery' => false,
            ],
            [
                'gtin' => '3145891073508',
                'name' => '50 ml',
                'amount' => 90.36,
                'url_path' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-494137/',
                'available' => true,
                'free_delivery' => false,
            ],
            [
                'gtin' => '3145891073607',
                'name' => '100 ml',
                'amount' => 117.0,
                'url_path' => '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-494164/',
                'available' => true,
                'free_delivery' => false,
            ],
        ];

        $result = $this->notinoScraper->scrape($url, $props);
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
        $this->createBotGetApolloStateDataExpectation([]);

        $this->expectException(UnableToExtractDataException::class);

        $this->notinoScraper->scrape($url, $props);
    }

    public function testScrapeWithNullApolloData(): void
    {
        $url = '/foo/bar';
        $props = [
            'lastmod' => null,
            'productIds' => [],
            'hasFailedScrapeDays' => false,
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation([]);
        $this->createBotGetApolloStateDataExpectation(null);

        $this->expectException(UnableToExtractDataException::class);

        $this->notinoScraper->scrape($url, $props);
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
            'category' => 'wax melt',
        ];

        $this->createBotCreateRequestExpectation([$url]);
        $this->createBotGetStructuredProductDataExpectation($productData);
        $this->createBotGetApolloStateDataExpectation([]);

        $result = $this->notinoScraper->scrape($url, $props);
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

    private function createBotGetApolloStateDataExpectation(?array $result): void
    {
        $this->bot
            ->shouldReceive('getApolloStateData')
            ->once()
            ->andReturn($result);
    }
}
