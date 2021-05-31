<?php

declare(strict_types=1);

namespace ParfumPulse\Twig;

use ParfumPulse\Twig\AssetIntegrityRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetIntegrityExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_integrity', [AssetIntegrityRuntime::class, 'getAssetIntegrity']),
        ];
    }
}
