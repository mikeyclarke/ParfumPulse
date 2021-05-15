<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\MerchantPage;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageUpdater;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageStorage;
use ParfumPulse\MerchantPage\MerchantPageValidator;

class MerchantPageUpdaterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantPageStorage;
    private LegacyMockInterface $merchantPageValidator;
    private MerchantPageUpdater $merchantPageUpdater;

    public function setUp(): void
    {
        $this->merchantPageStorage = m::mock(MerchantPageStorage::class);
        $this->merchantPageValidator = m::mock(MerchantPageValidator::class);

        $this->merchantPageUpdater = new MerchantPageUpdater(
            $this->merchantPageStorage,
            $this->merchantPageValidator,
        );
    }

    public function testUpdate(): void
    {
        $merchantPageId = 123;
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/p-123456/';
        $merchantPage = MerchantPageModel::createFromArray([
            'id' => $merchantPageId,
            'url_path' => $urlPath,
            'should_scrape' => true,
            'failed_scrape_days' => 2,
        ]);
        $parameters = [
            'should_scrape' => false,
            'failed_scrape_days' => 3,
        ];

        $merchantPageRow = array_merge(
            [
                'id' => $merchantPageId,
                'url_path' => $urlPath,
            ],
            $parameters
        );

        $this->createMerchantPageValidatorExpectation([$parameters]);
        $this->createMerchantPageStorageExpectation([$merchantPageId, $parameters], $merchantPageRow);

        $result = $this->merchantPageUpdater->update($merchantPage, $parameters);
        $this->assertEquals($merchantPage, $result);
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
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
