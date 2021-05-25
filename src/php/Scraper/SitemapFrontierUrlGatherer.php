<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\FrontierUrlGathererInterface;
use ParfumPulse\Scraper\UrlCollection;

class SitemapFrontierUrlGatherer implements FrontierUrlGathererInterface
{
    public function __construct(
        private array $merchantsConfig,
    ) {
    }

    public function gather(MerchantModel $merchant): UrlCollection
    {
        $config = $this->merchantsConfig[$merchant->getCode()];
        $sitemap = simplexml_load_file($config['sitemap_url']);
        if (false === $sitemap) {
            throw new \Exception('Failed to parse sitemap');
        }

        $collection = new UrlCollection();
        foreach ($sitemap as $entry) {
            $urlPath = $this->getUrlPath((string) $entry->loc);
            if (
                isset($config['sitemap_url_path_pattern']) &&
                !preg_match($config['sitemap_url_path_pattern'], $urlPath)
            ) {
                continue;
            }
            $collection->add($urlPath, (string) $entry->lastmod ?? null);
        }

        return $collection;
    }

    private function getUrlPath(string $url): string
    {
        $parts = parse_url($url);
        if (false === $parts) {
            throw new \Exception('Couldn’t parse URL');
        }
        return $parts['path'] ?? '';
    }
}
