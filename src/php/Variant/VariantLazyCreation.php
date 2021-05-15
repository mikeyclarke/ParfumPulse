<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Variant\VariantCreator;
use ParfumPulse\Variant\VariantModel;
use ParfumPulse\Variant\VariantRepository;

class VariantLazyCreation
{
    public function __construct(
        private VariantCreator $variantCreator,
        private VariantRepository $variantRepository,
    ) {
    }

    public function createOrRetrieve(FragranceModel $fragrance, string $name, ?string $gtin = null): VariantModel
    {
        if (null !== $gtin) {
            $result = $this->variantRepository->findOneByGtinOrName($gtin, $name, $fragrance->getId());
        } else {
            $result = $this->variantRepository->findOneByName($name, $fragrance->getId());
        }

        if (null === $result) {
            return $this->variantCreator->create($fragrance, [
                'name' => $name,
                'gtin' => $gtin,
            ]);
        }

        return VariantModel::createFromArray($result);
    }
}
