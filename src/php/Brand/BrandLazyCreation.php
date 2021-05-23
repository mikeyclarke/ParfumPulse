<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandNameNormalizer;
use ParfumPulse\Brand\BrandRepository;

class BrandLazyCreation
{
    public function __construct(
        private BrandCreator $brandCreator,
        private BrandNameNormalizer $brandNameNormalizer,
        private BrandRepository $brandRepository,
    ) {
    }

    public function createOrRetrieve(string $name): BrandModel
    {
        $normalized = $this->brandNameNormalizer->normalize($name);

        $result = $this->brandRepository->findOneByName($normalized);
        if (null === $result) {
            return $this->brandCreator->create($normalized);
        }
        return BrandModel::createFromArray($result);
    }
}
