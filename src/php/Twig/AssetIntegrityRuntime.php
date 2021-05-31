<?php

declare(strict_types=1);

namespace ParfumPulse\Twig;

use ParfumPulse\Asset\AssetIntegrity;
use RuntimeException;
use Twig\Extension\RuntimeExtensionInterface;

class AssetIntegrityRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private AssetIntegrity $assetIntegrity,
    ) {
    }

    public function getAssetIntegrity(string $path): ?string
    {
        return $this->assetIntegrity->getIntegrityHashesForAsset($path);
    }
}
