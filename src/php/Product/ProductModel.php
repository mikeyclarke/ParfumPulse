<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use ParfumPulse\ModelTrait;

final class ProductModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'url_path' => 'urlPath',
        'free_delivery' => 'freeDelivery',
        'variant_id' => 'variantId',
        'merchant_page_id' => 'merchantPageId',
        'merchant_id' => 'merchantId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private string $urlPath;
    private bool $freeDelivery;
    private int $variantId;
    private int $merchantPageId;
    private int $merchantId;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): ProductModel
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

    public function setFreeDelivery(bool $freeDelivery): void
    {
        $this->freeDelivery = $freeDelivery;
    }

    public function hasFreeDelivery(): bool
    {
        return $this->freeDelivery;
    }

    public function setVariantId(int $variantId): void
    {
        $this->variantId = $variantId;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function setMerchantPageId(int $merchantPageId): void
    {
        $this->merchantPageId = $merchantPageId;
    }

    public function getMerchantPageId(): int
    {
        return $this->merchantPageId;
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
