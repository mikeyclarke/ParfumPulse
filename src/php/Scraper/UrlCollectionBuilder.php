<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\MerchantPage\MerchantPageRepository;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\FrontierUrlGathererFactory;
use ParfumPulse\Scraper\UrlCollection;
use ParfumPulse\Scraper\UrlIgnoreList;

class UrlCollectionBuilder
{
    public function __construct(
        private FrontierUrlGathererFactory $frontierUrlGathererFactory,
        private MerchantPageRepository $merchantPageRepository,
        private UrlIgnoreList $urlIgnoreList,
        private array $merchantsConfig,
    ) {
    }

    public function build(MerchantModel $merchant): UrlCollection
    {
        $config = $this->merchantsConfig[$merchant->getCode()];

        $urlCollection = new UrlCollection();

        $frontierUrlGatherer = $this->frontierUrlGathererFactory->create($merchant);
        $frontierUrlCollection = $frontierUrlGatherer->gather($merchant);
        $urlCollection->addCollection($frontierUrlCollection);

        $knownUrls = $this->getKnownUrls($merchant);
        foreach ($knownUrls as $url => $props) {
            $entry = $urlCollection->get($url);
            if (null !== $entry) {
                $urlCollection->add($url, $entry['lastmod'], $props['productIds'], $props['hasFailedScrapeDays']);
                continue;
            }
            $urlCollection->add($url, null, $props['productIds']);
        }

        $useLastmod = $config['use_lastmod'] ?? false;
        if ($useLastmod) {
            $dateTime = new \DateTime();
            $dateTime->sub(new \DateInterval('P1D'));
            $datestamp = $dateTime->format('Y-m-d');

            $urlCollection->filter(function ($url, $props) use ($datestamp, $knownUrls) {
                if ($props['hasFailedScrapeDays']) {
                    return true;
                }

                if (!isset($knownUrls[$url])) {
                    return true;
                }

                if (null === $props['lastmod'] || $props['lastmod'] > $datestamp) {
                    return true;
                }

                return false;
            });
        }

        $ignoreList = $this->urlIgnoreList->get($merchant);
        $urlCollection->remove($ignoreList);

        return $urlCollection;
    }

    private function getKnownUrls(MerchantModel $merchant): array
    {
        $knownPages = $this->merchantPageRepository->getAllUrlsAndProductIds($merchant->getId());
        $result = [];
        foreach ($knownPages as $row) {
            $url = $row['url_path'];
            if (!isset($result[$url])) {
                $result[$url] = [
                    'productIds' => [],
                    'hasFailedScrapeDays' => $row['failed_scrape_days'] > 0,
                ];
            }
            $result[$url]['productIds'][] = $row['product_id'];
        }
        return $result;
    }
}
