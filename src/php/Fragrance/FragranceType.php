<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ReflectionClass;

final class FragranceType
{
    public const PARFUM = 'parfum';
    public const EAU_DE_PARFUM = 'eau de parfum';
    public const EAU_DE_TOILETTE = 'eau de toilette';
    public const EAU_DE_COLOGNE = 'eau_de_cologne';
    public const EAU_FRAICHE = 'eau fraiche';
    public const AFTERSHAVE_WATER = 'aftershave water';
    public const AFTERSHAVE_SPRAY = 'aftershave spray';
    public const EXTRAIT_DE_PARFUM = 'extrait de parfum';
    public const SOIE_DE_PARFUM = 'soie de parfum';
    public const DEFAULT_MALE_FRAGRANCE_TYPES = [
        self::EAU_DE_COLOGNE,
        self::AFTERSHAVE_WATER,
        self::AFTERSHAVE_SPRAY,
    ];

    public static function getAll(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return array_values($class->getConstants());
    }
}
