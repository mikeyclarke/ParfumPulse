<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\MerchantPage;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageCreator;
use ParfumPulse\MerchantPage\MerchantPageLazyCreation;
use ParfumPulse\MerchantPage\MerchantPageModel;
use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\MerchantPage\MerchantPageUpdater;
use ParfumPulse\Merchant\MerchantModel;

class MerchantPageLazyCreationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $merchantPageCreator;
    private LegacyMockInterface $merchantPageRepository;
    private LegacyMockInterface $merchantPageUpdater;
    private MerchantPageLazyCreation $merchantPageLazyCreation;

    public function setUp(): void
    {
        $this->merchantPageCreator = m::mock(MerchantPageCreator::class);
        $this->merchantPageRepository = m::mock(MerchantPageRepository::class);
        $this->merchantPageUpdater = m::mock(MerchantPageUpdater::class);

        $this->merchantPageLazyCreation = new MerchantPageLazyCreation(
            $this->merchantPageCreator,
            $this->merchantPageRepository,
            $this->merchantPageUpdater,
        );
    }

    public function testCreateOrUpdateWhenPageAlreadyExistsAndParametersAreUpToDate(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/';
        $parameters = [
            'should_scrape' => true,
            'failed_scrape_days' => 0,
        ];

        $merchantPageRow = array_merge(
            $parameters,
            [
                'url_path' => $urlPath,
                'merchant_id' => $merchantId,
            ]
        );

        $this->createMerchantPageRepositoryExpectation([$urlPath, $merchantId], $merchantPageRow);

        $result = $this->merchantPageLazyCreation->createOrUpdate($merchant, $urlPath, $parameters);
        $this->assertInstanceOf(MerchantPageModel::class, $result);
    }

    public function testCreateOrUpdateWhenPageAlreadyExistsAndParametersAreOutdated(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/';
        $parameters = [
            'should_scrape' => false,
            'failed_scrape_days' => 3,
        ];

        $merchantPageRow = [
            'url_path' => $urlPath,
            'should_scrape' => true,
            'failed_scrape_days' => 2,
            'merchant_id' => $merchantId,
        ];
        $merchantPage = new MerchantPageModel();

        $this->createMerchantPageRepositoryExpectation([$urlPath, $merchantId], $merchantPageRow);
        $this->createMerchantPageUpdaterExpectation([m::type(MerchantPageModel::class), $parameters], $merchantPage);

        $result = $this->merchantPageLazyCreation->createOrUpdate($merchant, $urlPath, $parameters);
        $this->assertEquals($merchantPage, $result);
    }

    public function testCreateOrUpdateWhenPageDoesNotYetExist(): void
    {
        $merchantId = 123;
        $merchant = MerchantModel::createFromArray(['id' => $merchantId]);
        $urlPath = '/chanel/bleu-de-chanel-eau-de-parfum-for-men/';
        $parameters = [
            'should_scrape' => true,
            'failed_scrape_days' => 0,
        ];

        $merchantPage = new MerchantPageModel();

        $this->createMerchantPageRepositoryExpectation([$urlPath, $merchantId], null);
        $this->createMerchantPageCreatorExpectation(
            [$merchant, array_merge($parameters, ['url_path' => $urlPath])],
            $merchantPage
        );

        $result = $this->merchantPageLazyCreation->createOrUpdate($merchant, $urlPath, $parameters);
        $this->assertEquals($merchantPage, $result);
    }

    private function createMerchantPageRepositoryExpectation(array $args, ?array $result): void
    {
        $this->merchantPageRepository
            ->shouldReceive('findOneByUrlPath')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMerchantPageCreatorExpectation(array $args, MerchantPageModel $result): void
    {
        $this->merchantPageCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMerchantPageUpdaterExpectation(array $args, MerchantPageModel $result): void
    {
        $this->merchantPageUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
