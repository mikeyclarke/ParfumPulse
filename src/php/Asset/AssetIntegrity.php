<?php

declare(strict_types=1);

namespace ParfumPulse\Asset;

use RuntimeException;

class AssetIntegrity
{
    private ?array $manifestData = null;

    public function __construct(
        private string $manifestPath,
    ) {
    }

    public function getIntegrityHashesForAsset(string $path): string
    {
        return $this->getFromManifest($path) ?? '';
    }

    private function getFromManifest(string $path): ?string
    {
        if (null === $this->manifestData) {
            if (!file_exists($this->manifestPath)) {
                throw new RuntimeException(
                    sprintf('Integrity manifest file "%s" does not exist.', $this->manifestPath)
                );
            }

            $fileContents = file_get_contents($this->manifestPath);
            if (false === $fileContents) {
                throw new RuntimeException(sprintf('Could not read integrity manifest file "%s"', $this->manifestPath));
            }

            $decoded = json_decode($fileContents, true, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new RuntimeException('Manifest should be an array');
            }

            $this->manifestData = $decoded;
        }

        return $this->manifestData[$path] ?? null;
    }
}
