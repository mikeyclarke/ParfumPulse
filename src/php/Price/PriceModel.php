<?php

declare(strict_types=1);

namespace ParfumPulse\Price;

use ParfumPulse\ModelTrait;

final class PriceModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'amount' => 'amount',
        'time_from' => 'timeFrom',
        'time_to' => 'timeTo',
        'product_id' => 'productId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private float $amount;
    private string $timeFrom;
    private ?string $timeTo;
    private int $productId;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): PriceModel
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

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setTimeFrom(string $timeFrom): void
    {
        $this->timeFrom = $timeFrom;
    }

    public function getTimeFrom(): string
    {
        return $this->timeFrom;
    }

    public function setTimeTo(?string $timeTo): void
    {
        $this->timeTo = $timeTo;
    }

    public function getTimeTo(): ?string
    {
        return $this->timeTo;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductId(): int
    {
        return $this->productId;
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
