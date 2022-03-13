<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Merchant\MerchantModel;

class UrlIgnoreList
{
    public function __construct(
        private array $merchantsConfig,
    ) {
    }

    public function get(MerchantModel $merchant): array
    {
        $path = $this->getPath($merchant);
        if (!file_exists($path)) {
            return [];
        }
        $file = file_get_contents($path);
        if (false === $file) {
            throw new \Exception('Couldn’t read file');
        }
        $decoded = json_decode($file, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('URL ignore list should be an array');
        }
        return $decoded;
    }

    public function add(MerchantModel $merchant, array $urls): void
    {
        $existingUrls = $this->get($merchant);
        $updated = array_merge($existingUrls, $urls);
        $path = $this->getPath($merchant);
        file_put_contents($path, json_encode($updated));
    }

    private function getPath(MerchantModel $merchant): string
    {
        $config = $this->merchantsConfig[$merchant->getCode()];
        if (!isset($config['ignore_list_file'])) {
            throw new \RuntimeException('Merchant ignore list file not specified.');
        }
        return $config['ignore_list_file'];
    }
}
