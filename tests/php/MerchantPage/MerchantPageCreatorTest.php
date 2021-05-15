<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\MerchantPage;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageCreator;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageStorage;
use ParfumPulse\MerchantPage\MerchantPageValidator;
use ParfumPulse\Merchant\MerchantModel;

class MerchantPageCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantPageStorage;
    private LegacyMockInterface $merchantPageValidator;
    private MerchantPageCreator $merchantPageCreator;

    public function setUp(): void
    {
        $this->merchantPageStorage = m::mock(MerchantPageStorage::class);
        $this->merchantPageValidator = m::mock(MerchantPageValidator::class);

        $this->merchantPageCreator = new MerchantPageCreator(
            $this->merchantPageStorage,
            $this->merchantPageValidator,
        );
    }

    public function testCreate(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/';
        $parameters = [
            'url_path' => $urlPath,
        ];

        $merchantPageRow = array_merge(
            $parameters,
            [
                'id' => 456,
                'merchant_id' => $merchantId,
                'should_scrape' => true,
                'failed_scrape_days' => 0,
            ]
        );

        $this->createMerchantPageValidatorExpectation([$parameters, true]);
        $this->createMerchantPageStorageExpectation([$urlPath, $merchantId, true, 0], $merchantPageRow);

        $result = $this->merchantPageCreator->create($merchant, $parameters);
        $this->assertInstanceOf(MerchantPageModel::class, $result);
        $this->assertEquals($urlPath, $result->getUrlPath());
        $this->assertEquals($merchantId, $result->getMerchantId());
        $this->assertTrue($result->shouldScrape());
        $this->assertEquals(0, $result->getFailedScrapeDays());
    }

    public function testCreateWithOptionalParameters(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/';
        $parameters = [
            'url_path' => $urlPath,
            'should_scrape' => false,
            'failed_scrape_days' => 3,
        ];

        $merchantPageRow = array_merge(
            $parameters,
            [
                'id' => 456,
                'merchant_id' => $merchantId,
            ]
        );

        $this->createMerchantPageValidatorExpectation([$parameters, true]);
        $this->createMerchantPageStorageExpectation(
            [$urlPath, $merchantId, $parameters['should_scrape'], $parameters['failed_scrape_days']],
            $merchantPageRow
        );

        $result = $this->merchantPageCreator->create($merchant, $parameters);
        $this->assertInstanceOf(MerchantPageModel::class, $result);
        $this->assertEquals($urlPath, $result->getUrlPath());
        $this->assertEquals($merchantId, $result->getMerchantId());
        $this->assertFalse($result->shouldScrape());
        $this->assertEquals($parameters['failed_scrape_days'], $result->getFailedScrapeDays());
    }

    private function createMerchantPageValidatorExpectation(array $args): void
    {
        $this->merchantPageValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createMerchantPageStorageExpectation(array $args, array $result): void
    {
        $this->merchantPageStorage
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
