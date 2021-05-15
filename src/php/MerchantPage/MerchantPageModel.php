<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use ParfumPulse\ModelTrait;

final class MerchantPageModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'url_path' => 'urlPath',
        'should_scrape' => 'shouldScrape',
        'failed_scrape_days' => 'failedScrapeDays',
        'merchant_id' => 'merchantId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private string $urlPath;
    private bool $shouldScrape;
    private int $failedScrapeDays;
    private int $merchantId;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): MerchantPageModel
    {
        $model = new static();
        $model->mapProperties($properties);
        return $model;
    }

    public function updateFromArray(array $properties): void
    {
        $this->mapProperties($properties);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUrlPath(string $urlPath): void
    {
        $this->urlPath = $urlPath;
    }

    public function getUrlPath(): string
    {
        return $this->urlPath;
    }

    public function setShouldScrape(bool $shouldScrape): void
    {
        $this->shouldScrape = $shouldScrape;
    }

    public function shouldScrape(): bool
    {
        return $this->shouldScrape;
    }

    public function setFailedScrapeDays(int $failedScrapeDays): void
    {
        $this->failedScrapeDays = $failedScrapeDays;
    }

    public function getFailedScrapeDays(): int
    {
        return $this->failedScrapeDays;
    }

    public function setMerchantId(int $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

    public function getUpdated(): string
    {
        return $this->updated;
    }
}
