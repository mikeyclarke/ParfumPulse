<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceStorage;
use ParfumPulse\Fragrance\FragranceValidator;
use ParfumPulse\Url\UrlIdGenerator;
use ParfumPulse\Url\UrlSlugGenerator;

class FragranceCreator
{
    public function __construct(
        private FragranceStorage $fragranceStorage,
        private FragranceValidator $fragranceValidator,
        private UrlIdGenerator $urlIdGenerator,
        private UrlSlugGenerator $urlSlugGenerator,
    ) {
    }

    public function create(BrandModel $brand, array $parameters): FragranceModel
    {
        $this->fragranceValidator->validate($parameters, true);

        $urlId = $this->urlIdGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generate($parameters['name']);

        $result = $this->fragranceStorage->insert(
            $parameters['name'],
            $parameters['gender'],
            $parameters['type'],
            $urlId,
            $urlSlug,
            $brand->getId()
        );

        return FragranceModel::createFromArray($result);
    }
}
