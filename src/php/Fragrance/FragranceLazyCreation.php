<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceCreator;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceRepository;
use ParfumPulse\Typography\StringNormalizer;

class FragranceLazyCreation
{
    public function __construct(
        private FragranceCreator $fragranceCreator,
        private FragranceRepository $fragranceRepository,
        private StringNormalizer $stringNormalizer,
    ) {
    }

    public function createOrRetrieve(BrandModel $brand, string $name, string $gender, string $type): FragranceModel
    {
        $normalized = $this->stringNormalizer->normalize($name);

        $criteria = [
            'brand_id' => $brand->getId(),
            'name' => $normalized,
            'gender' => $gender,
            'type' => $type,
        ];
        $result = $this->fragranceRepository->findOneBy($criteria);

        if (null === $result) {
            return $this->fragranceCreator->create($brand, [
                'name' => $normalized,
                'gender' => $gender,
                'type' => $type,
            ]);
        }

        return FragranceModel::createFromArray($result);
    }
}
