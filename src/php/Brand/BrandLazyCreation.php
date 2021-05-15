<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Brand\BrandCreator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;

class BrandLazyCreation
{
    public function __construct(
        private BrandCreator $brandCreator,
        private BrandRepository $brandRepository,
    ) {
    }

    public function createOrRetrieve(string $name): BrandModel
    {
        $result = $this->brandRepository->findOneByName($name);
        if (null === $result) {
            return $this->brandCreator->create($name);
        }
        return BrandModel::createFromArray($result);
    }
}
