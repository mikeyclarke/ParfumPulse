<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Fragrance\FragranceGender;
use ParfumPulse\Fragrance\FragranceType;
use ParfumPulse\Scraper\Exception\UnableToExtractDataException;
use ParfumPulse\Scraper\ScraperBot;
use ParfumPulse\Scraper\ScraperInterface;
use ParfumPulse\Scraper\ScraperResult;
use ParfumPulse\Variant\GtinNormalizer;

class AllbeautyScraper implements ScraperInterface
{
    // phpcs:disable Generic.Files.LineLength
    private const FRAGRANCE_TYPE_PATTERN = '~^(intense|limited edition|refillable|pure)? ?(aftershave|aftershave lotion|eau de cologne|cologne|eau de parfum|soie de parfum|eau de toilette|extrait de parfum|parfum|perfume) ?(intense|natural|travel|refillable|non refillable|refillable bottle)? ?(spray|splash)? ?(for him|intense)? ([0-9]+)ml( \/ [0-9.]+ (fl.)?oz.)?$~';
    // phpcs:enable

    private ScraperBot $bot;

    public function setBot(ScraperBot $bot): void
    {
        $this->bot = $bot;
    }

    public function scrape(string $url, array $props): ScraperResult
    {
        $this->bot->createRequest($url);

        $productData = $this->bot->getStructuredProductData();
        if (null === $productData) {
            throw new UnableToExtractDataException();
        }

        $model = $productData['model'];
        if (!preg_match(self::FRAGRANCE_TYPE_PATTERN, trim(strtolower($model)), $matches, PREG_UNMATCHED_AS_NULL)) {
            return new ScraperResult($url, $props['productIds'], isRelevant: false);
        }

        $concentration = $matches[2];
        $applicationType = $matches[4];
        $size = $matches[6];

        list($brand, $fragrance, $variants) = $this->parse($productData, $url, $concentration, $applicationType, $size);

        return new ScraperResult($url, $props['productIds'], $brand, $fragrance, $variants);
    }

    private function parse(
        array $productData,
        string $url,
        string $concentration,
        ?string $applicationType,
        string $size
    ): array {
        $fragranceType = $this->getType($concentration, $applicationType);

        $brand = ['name' => $productData['brand']['name']];

        $fragrance = [
            'name' => $this->getFragranceName($productData['name'], $brand['name'], $productData['model']),
            'type' => $fragranceType,
            'gender' => $this->getGender($productData),
        ];

        $model = strtolower($productData['model']);
        if (str_contains($model, 'limited edition')) {
            $fragrance['name'] .= ' Limited Edition';
        }

        if (str_contains($model, 'refillable')) {
            $fragrance['name'] .= ' (refillable)';
        }

        $price = $this->getPrice($productData);

        $variants = [];

        if (null !== $price) {
            $gtin = trim($productData['gtin13']);
            $variants[] = [
                'gtin' => (strlen($gtin) >= 8) ? GtinNormalizer::normalize($gtin) : null,
                'name' => sprintf('%s ml', $size),
                'amount' => $price,
                'url_path' => $url,
                'available' => true,
                'free_delivery' => false,
            ];
        }

        return [$brand, $fragrance, $variants];
    }

    private function getPrice(array $productData): ?float
    {
        $price = null;
        if (isset($productData['offers'])) {
            foreach ($productData['offers'] as $offer) {
                if ($offer['priceCurrency'] === 'GBP') {
                    $price = $offer['price'];
                }
            }
        }
        return $price;
    }

    private function getGender(array $productData): string
    {
        $audience = $productData['audience']['name'] ?? '';

        if (strtolower($audience) === 'female') {
            return FragranceGender::FEMALE;
        }

        if (strtolower($audience) === 'male') {
            return FragranceGender::MALE;
        }

        if (strtolower($audience) === 'unisex') {
            return FragranceGender::UNISEX;
        }

        if (in_array($audience, FragranceType::DEFAULT_MALE_FRAGRANCE_TYPES)) {
            return FragranceGender::MALE;
        }

        return FragranceGender::UNISEX;
    }

    private function getFragranceName(string $name, string $brandName, string $model): string
    {
        $fragranceName = $name;
        if (str_starts_with($fragranceName, $brandName)) {
            $fragranceName = mb_substr($fragranceName, mb_strlen($brandName) + 1);
        }

        $typePos = mb_strpos($fragranceName, trim($model));
        if ($typePos) {
            $fragranceName = mb_substr($fragranceName, 0, $typePos - 1);
        }

        return $fragranceName;
    }

    private function getType(string $concentration, ?string $applicationType): string
    {
        if (str_contains($concentration, 'aftershave')) {
            return ($applicationType === 'spray') ? FragranceType::AFTERSHAVE_SPRAY : FragranceType::AFTERSHAVE_WATER;
        }

        switch ($concentration) {
            case 'cologne':
            case 'eau de cologne':
                return FragranceType::EAU_DE_COLOGNE;
            case 'eau de parfum':
                return FragranceType::EAU_DE_PARFUM;
            case 'soie de parfum':
                return FragranceType::SOIE_DE_PARFUM;
            case 'eau de toilette':
                return FragranceType::EAU_DE_TOILETTE;
            case 'extrait de parfum':
                return FragranceType::EXTRAIT_DE_PARFUM;
            case 'parfum':
            case 'perfume':
                return FragranceType::PARFUM;
        }

        throw new \Exception('Unsupported type');
    }
}
