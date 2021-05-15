<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Variant\VariantModel;
use ParfumPulse\Variant\VariantStorage;
use ParfumPulse\Variant\VariantValidator;

class VariantCreator
{
    public function __construct(
        private VariantStorage $variantStorage,
        private VariantValidator $variantValidator,
    ) {
    }

    public function create(FragranceModel $fragrance, array $parameters): VariantModel
    {
        $this->variantValidator->validate($parameters, true);

        $result = $this->variantStorage->insert(
            $parameters['name'],
            $parameters['gtin'] ?? null,
            $fragrance->getId(),
        );

        return VariantModel::createFromArray($result);
    }
}
