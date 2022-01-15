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

class NotinoScraper implements ScraperInterface
{
    private const RELEVANT_CATEGORIES = [
        'perfume for women',
        'eau de parfum for women',
        'eau de parfum refillable for women',
        'eau de parfum for women limited edition',
        'eau de parfum limited edition for women',
        'eau de parfum (limited edition) for women',
        'eau de parfum with atomizer for women',
        'eau de toilette for women',
        'eau de toilette limited edition for women',
        'eau de toilette refillable for women',
        'eau de toilette with atomizer for women',
        'eau de cologne for women',
        'eau fraiche for women',
        'perfume extract for women',
        'perfume for men',
        'eau de parfum for men',
        'eau de parfum limited edition for men',
        'eau de parfum refillable for men',
        'eau de toilette for men',
        'eau de toilette without atomizer for men',
        'eau de toilette refillable for men',
        'eau de toilette limited edition for men',
        'eau de cologne',
        'eau de cologne for men',
        'eau de cologne without atomiser for men',
        'eau fraiche for men',
        'perfume extract for men',
        'aftershave water',
        'aftershave water for men',
        'aftershave water without atomizer for men',
        'aftershave water with atomizer for men',
        'after-shave spray for men',
        'perfume unisex',
        'perfume refillable unisex',
        'eau de parfum',
        'eau de parfum unisex',
        'eau de toilette',
        'eau de toilette unisex',
        'eau de toilette refillable unisex',
        'eau de cologne unisex',
        'eau de cologne without atomiser unisex',
        'eau de cologne with atomizer unisex',
        'eau fraiche unisex',
        'perfume extract unisex',
    ];

    private ScraperBot $bot;

    public function setBot(ScraperBot $bot): void
    {
        $this->bot = $bot;
    }

    public function scrape(string $url, array $props): ScraperResult
    {
        $this->bot->createRequest($url);

        $productData = $this->bot->getStructuredProductData();
        $apolloStateData = $this->bot->getApolloStateData();

        if (null === $productData || null === $apolloStateData) {
            throw new UnableToExtractDataException();
        }

        if (!$this->isFragrance($productData)) {
            return new ScraperResult($url, $props['productIds'], isRelevant: false);
        }

        list($brand, $fragrance, $variants) = $this->parse($productData, $apolloStateData);

        return new ScraperResult($url, $props['productIds'], $brand, $fragrance, $variants);
    }

    private function parse(array $productData, array $apolloStateData): array
    {
        $category = strtolower($productData['category']);
        $fragranceType = $this->getType($category);

        $brand = ['name' => $this->decodeText($productData['brand']['name'])];
        $fragrance = [
            'name' => $this->getFragranceName($this->decodeText($productData['name']), $brand['name']),
            'type' => $fragranceType,
            'gender' => $this->getGender($category, $fragranceType),
        ];

        if (str_contains($category, 'limited edition')) {
            $fragrance['name'] .= ' Limited Edition';
        }

        if (str_contains($category, 'refillable')) {
            $fragrance['name'] .= ' (refillable)';
        }

        if (str_contains($category, 'with atomizer')) {
            $fragrance['name'] .= ' (with atomizer)';
        }

        $variants = [];
        foreach ($apolloStateData as $key => $data) {
            if (!preg_match('/^Variant:[0-9]+$/', $key)) {
                continue;
            }

            if (isset($data['attributes']['json']['Damage'])) {
                continue;
            }

            $size = $this->getSize($data, $apolloStateData);
            if (null === $size) {
                continue;
            }

            $amount = $this->getPrice($data, $apolloStateData);
            if (null === $amount) {
                continue;
            }

            $variants[] = [
                'gtin' => (strlen($data['eanCode']) >= 8) ? GtinNormalizer::normalize($data['eanCode']) : null,
                'name' => $size,
                'amount' => $amount,
                'url_path' => $data['url'],
                'available' => $data['canBuy'],
                'free_delivery' => isset($data['attributes']['json']['FreeDelivery']) &&
                    true === (bool) $data['attributes']['json']['FreeDelivery'],
            ];
        }

        return [$brand, $fragrance, $variants];
    }

    private function getPrice(array $variantData, array $apolloStateData): ?float
    {
        if (!isset($variantData['price']) || !is_array($variantData['price'])) {
            return null;
        }

        $priceData = $variantData['price'];
        if (!isset($priceData['currency']) || $priceData['currency'] !== 'GBP') {
            return null;
        }

        if (!isset($priceData['value'])) {
            return null;
        }

        return (float) $priceData['value'];
    }

    private function getSize(array $variantData, array $apolloStateData): ?string
    {
        $parametersKey = $variantData['parameters']['id'] ?? null;

        if (
            isset($variantData['additionalInfo']) &&
            preg_match('/^[0-9.]+ ml$/', trim($variantData['additionalInfo']))
        ) {
            return isset($apolloStateData[$parametersKey]) ?
                sprintf('%d ml', $apolloStateData[$parametersKey]['amount']) : $variantData['additionalInfo'];
        }

        if (
            isset($variantData['variantName']) &&
            preg_match('/([0-9.]+ ml)$/', trim($variantData['variantName']), $matches)
        ) {
            return $matches[1];
        }

        return null;
    }

    private function getGender(string $category, string $type): string
    {
        if (str_contains($category, 'for women')) {
            return FragranceGender::FEMALE;
        }

        if (str_contains($category, 'for men')) {
            return FragranceGender::MALE;
        }

        if (str_contains($category, 'unisex')) {
            return FragranceGender::UNISEX;
        }

        if (in_array($type, FragranceType::DEFAULT_MALE_FRAGRANCE_TYPES)) {
            return FragranceGender::MALE;
        }

        return FragranceGender::UNISEX;
    }

    private function getFragranceName(string $name, string $brandName): string
    {
        if (str_starts_with($name, $brandName)) {
            return mb_substr($name, mb_strlen($brandName) + 1);
        }

        return $name;
    }

    private function getType(string $category): string
    {
        if (str_starts_with($category, 'perfume extract')) {
            return FragranceType::EXTRAIT_DE_PARFUM;
        }

        if (str_starts_with($category, 'perfume')) {
            return FragranceType::PARFUM;
        }

        if (str_starts_with($category, 'eau de parfum')) {
            return FragranceType::EAU_DE_PARFUM;
        }

        if (str_starts_with($category, 'eau de toilette')) {
            return FragranceType::EAU_DE_TOILETTE;
        }

        if (str_starts_with($category, 'eau de cologne')) {
            return FragranceType::EAU_DE_COLOGNE;
        }

        if (str_starts_with($category, 'eau fraiche')) {
            return FragranceType::EAU_FRAICHE;
        }

        if (str_starts_with($category, 'aftershave water')) {
            return FragranceType::AFTERSHAVE_WATER;
        }

        if (str_starts_with($category, 'after-shave spray')) {
            return FragranceType::AFTERSHAVE_SPRAY;
        }

        throw new \Exception('Unsupported type');
    }

    private function decodeText(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function isFragrance(array $productData): bool
    {
        if (!isset($productData['category'])) {
            return false;
        }

        return in_array(strtolower($productData['category']), self::RELEVANT_CATEGORIES);
    }
}
