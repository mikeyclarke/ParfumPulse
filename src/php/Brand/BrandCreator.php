<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandStorage;
use ParfumPulse\Brand\BrandValidator;
use ParfumPulse\Url\UrlSlugGenerator;

class BrandCreator
{
    public function __construct(
        private BrandStorage $brandStorage,
        private BrandValidator $brandValidator,
        private UrlSlugGenerator $urlSlugGenerator,
    ) {
    }

    public function create(string $name): BrandModel
    {
        $this->brandValidator->validate(['name' => $name], true);

        $urlSlug = $this->urlSlugGenerator->generate($name);
        $result = $this->brandStorage->insert($name, $urlSlug);

        return BrandModel::createFromArray($result);
    }
}
