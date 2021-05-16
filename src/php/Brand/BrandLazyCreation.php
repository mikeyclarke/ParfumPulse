<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;
use ParfumPulse\Typography\StringNormalizer;

class BrandLazyCreation
{
    public function __construct(
        private BrandCreator $brandCreator,
        private BrandRepository $brandRepository,
        private StringNormalizer $stringNormalizer,
    ) {
    }

    public function createOrRetrieve(string $name): BrandModel
    {
        $normalized = $this->stringNormalizer->normalize($name);

        $result = $this->brandRepository->findOneByName($normalized);
        if (null === $result) {
            return $this->brandCreator->create($normalized);
        }
        return BrandModel::createFromArray($result);
    }
}
