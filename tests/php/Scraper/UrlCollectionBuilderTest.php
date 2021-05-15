<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Scraper;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\FrontierUrlGathererFactory;
use ParfumPulse\Scraper\FrontierUrlGathererInterface;
use ParfumPulse\Scraper\UrlCollection;
use ParfumPulse\Scraper\UrlCollectionBuilder;
use ParfumPulse\Scraper\UrlIgnoreList;

class UrlCollectionBuilderTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $frontierUrlGathererFactory;
    private LegacyMockInterface $merchantPageRepository;
    private LegacyMockInterface $urlIgnoreList;
    private array $merchantsConfig;
    private UrlCollectionBuilder $urlCollectionBuilder;
    private LegacyMockInterface $frontierUrlGatherer;

    public function setUp(): void
    {
        $this->frontierUrlGathererFactory = m::mock(FrontierUrlGathererFactory::class);
        $this->merchantPageRepository = m::mock(MerchantPageRepository::class);
        $this->urlIgnoreList = m::mock(UrlIgnoreList::class);
        $this->merchantsConfig = [
            'notino' => [
                'use_lastmod' => true,
            ],
        ];

        $this->urlCollectionBuilder = new UrlCollectionBuilder(
            $this->frontierUrlGathererFactory,
            $this->merchantPageRepository,
            $this->urlIgnoreList,
            $this->merchantsConfig,
        );

        $this->frontierUrlGatherer = m::mock(FrontierUrlGathererInterface::class);
    }

    public function testBuild(): void
    {
        $merchantId = 123;
        $merchantCode = 'notino';
        $merchant = new MerchantModel();
        $merchant->setId($merchantId);
        $merchant->setCode($merchantCode);

        $frontierUrls = [
            '/foo/foo' => null,
            '/foo/bar' => (new \DateTime())->sub(new \DateInterval('P10D'))->format('Y-m-d'),
            '/foo/baz' => (new \DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d'),
            '/foo/qux' => (new \DateTime())->format('Y-m-d'),
            '/foo/quux' => (new \DateTime())->sub(new \DateInterval('P3D'))->format('Y-m-d'),
            '/foo/quz' => (new \DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d'),
            '/foo/quuz' => (new \DateTime())->format('Y-m-d'),
            '/foo/grault' => (new \DateTime())->sub(new \DateInterval('P5D'))->format('Y-m-d'),
        ];

        $frontierUrlCollection = new UrlCollection();
        foreach ($frontierUrls as $url => $lastmod) {
            $frontierUrlCollection->add($url, $lastmod);
        }
        $knownPages = [
            ['url_path' => '/foo/qux', 'failed_scrape_days' => 0, 'product_id' => 456],
            ['url_path' => '/foo/qux', 'failed_scrape_days' => 0, 'product_id' => 789],
            ['url_path' => '/foo/corge', 'failed_scrape_days' => 0, 'product_id' => 012],
            ['url_path' => '/foo/bar', 'failed_scrape_days' => 0, 'product_id' => 345],
            ['url_path' => '/foo/grault', 'failed_scrape_days' => 2, 'product_id' => 678],
        ];
        $ignoreList = [
            '/foo/baz',
            '/foo/quuz',
        ];

        $expected = [
            '/foo/foo' => [
                'lastmod' => null,
                'productIds' => [],
                'hasFailedScrapeDays' => false,
            ],
            '/foo/qux' => [
                'lastmod' => $frontierUrls['/foo/qux'],
                'productIds' => [456, 789],
                'hasFailedScrapeDays' => false,
            ],
            '/foo/quux' => [
                'lastmod' => $frontierUrls['/foo/quux'],
                'productIds' => [],
                'hasFailedScrapeDays' => false,
            ],
            '/foo/quz' => [
                'lastmod' => $frontierUrls['/foo/quz'],
                'productIds' => [],
                'hasFailedScrapeDays' => false,
            ],
            '/foo/grault' => [
                'lastmod' => $frontierUrls['/foo/grault'],
                'productIds' => [678],
                'hasFailedScrapeDays' => true,
            ],
            '/foo/corge' => [
                'lastmod' => null,
                'productIds' => [012],
                'hasFailedScrapeDays' => false,
            ],
        ];

        $this->createFrontierUrlGathererFactoryExpectation([$merchant]);
        $this->createFrontierUrlGathererExpectation([$merchant], $frontierUrlCollection);
        $this->createMerchantPageRepositoryExpectation([$merchantId], $knownPages);
        $this->createUrlIgnoreListExpectation([$merchant], $ignoreList);

        $result = $this->urlCollectionBuilder->build($merchant);
        $this->assertInstanceOf(UrlCollection::class, $result);
        $this->assertEquals($expected, $result->all());
    }

    private function createFrontierUrlGathererFactoryExpectation(array $args): void
    {
        $this->frontierUrlGathererFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($this->frontierUrlGatherer);
    }

    private function createFrontierUrlGathererExpectation(array $args, UrlCollection $result): void
    {
        $this->frontierUrlGatherer
            ->shouldReceive('gather')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createMerchantPageRepositoryExpectation(array $args, array $result): void
    {
        $this->merchantPageRepository
            ->shouldReceive('getAllUrlsAndProductIds')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createUrlIgnoreListExpectation(array $args, array $result): void
    {
        $this->urlIgnoreList
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
