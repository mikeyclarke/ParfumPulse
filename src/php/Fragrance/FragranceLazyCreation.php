<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceCreator;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceRepository;

class FragranceLazyCreation
{
    public function __construct(
        private FragranceCreator $fragranceCreator,
        private FragranceRepository $fragranceRepository,
    ) {
    }

    public function createOrRetrieve(BrandModel $brand, string $name, string $gender, string $type): FragranceModel
    {
        $criteria = [
            'brand_id' => $brand->getId(),
            'name' => $name,
            'gender' => $gender,
            'type' => $type,
        ];
        $result = $this->fragranceRepository->findOneBy($criteria);

        if (null === $result) {
            return $this->fragranceCreator->create($brand, [
                'name' => $name,
                'gender' => $gender,
                'type' => $type,
            ]);
        }

        return FragranceModel::createFromArray($result);
    }
}
