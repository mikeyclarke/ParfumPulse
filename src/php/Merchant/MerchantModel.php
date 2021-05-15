<?php

declare(strict_types=1);

namespace ParfumPulse\Merchant;

use ParfumPulse\ModelTrait;

final class MerchantModel
{
    use ModelTrait;

    private const FIELD_MAP = [
        'id' => 'id',
        'code' => 'code',
    ];

    private int $id;
    private string $code;

    public static function createFromArray(array $properties): MerchantModel
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

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
