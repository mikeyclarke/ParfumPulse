<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ParfumPulse\ModelTrait;

final class FragranceModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'gender' => 'gender',
        'type' => 'type',
        'brand_id' => 'brandId',
        'url_id' => 'urlId',
        'url_slug' => 'urlSlug',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private string $name;
    private string $gender;
    private string $type;
    private int $brandId;
    private string $urlId;
    private string $urlSlug;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): FragranceModel
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

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setBrandId(int $brandId): void
    {
        $this->brandId = $brandId;
    }

    public function getBrandId(): int
    {
        return $this->brandId;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setUrlSlug(string $urlSlug): void
    {
        $this->urlSlug = $urlSlug;
    }

    public function getUrlSlug(): string
    {
        return $this->urlSlug;
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
