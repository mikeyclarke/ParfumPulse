<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

use ParfumPulse\ModelTrait;

final class VariantModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'gtin' => 'gtin',
        'fragrance_id' => 'fragranceId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private string $name;
    private ?int $gtin;
    private int $fragranceId;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): VariantModel
    {
        $model = new static();
        $model->mapProperties($properties);
        return $model;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setGtin(?int $gtin): void
    {
        $this->gtin = $gtin;
    }

    public function getGtin(): ?int
    {
        return $this->gtin;
    }

    public function setFragranceId(int $fragranceId): void
    {
        $this->fragranceId = $fragranceId;
    }

    public function getFragranceId(): int
    {
        return $this->fragranceId;
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
