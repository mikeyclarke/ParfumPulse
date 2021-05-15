<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ReflectionClass;

final class FragranceGender
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const UNISEX = 'unisex';

    public static function getAll(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_values($class->getConstants());
    }
}
