<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\ModelTrait;

final class BrandModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'name' => 'name',
        'url_slug' => 'urlSlug',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private int $id;
    private string $name;
    private string $urlSlug;
    private string $created;
    private string $updated;

    public static function createFromArray(array $properties): BrandModel
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
