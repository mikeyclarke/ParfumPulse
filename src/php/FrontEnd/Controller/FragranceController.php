<?php

declare(strict_types=1);

namespace ParfumPulse\FrontEnd\Controller;

use NumberFormatter;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceRepository;
use ParfumPulse\Variant\VariantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class FragranceController
{
    public function __construct(
        private BrandRepository $brandRepository,
        private FragranceRepository $fragranceRepository,
        private Twig $twig,
        private VariantRepository $variantRepository,
        private array $merchantsConfig,
    ) {
    }

    public function getAction(Request $request): Response
    {
        $brandUrlSlug = $request->attributes->getAlnum('brand_url_slug');
        $fragranceUrlId = $request->attributes->get('fragrance_url_id');
        $fragranceUrlSlug = $request->attributes->get('fragrance_url_slug');

        $result = $this->brandRepository->findOneByUrlSlug($brandUrlSlug);
        if (null === $result) {
            throw new NotFoundHttpException();
        }
        $brand = BrandModel::createFromArray($result);

        $result = $this->fragranceRepository->findOneBy([
            'brand_id' => $brand->getId(),
            'url_id' => $fragranceUrlId,
            'url_slug' => $fragranceUrlSlug,
        ]);
        if (null === $result) {
            throw new NotFoundHttpException();
        }
        $fragrance = FragranceModel::createFromArray($result);

        $result = $this->variantRepository->getFragranceVariantsData($fragrance->getId());
        $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
        $variants = [];
        foreach ($result as $row) {
            $name = $row['name'];
            if (!isset($variants[$name])) {
                $variants[$name] = [];
            }
            $amount = null;
            if (null !== $row['amount']) {
                $amount = $numberFormatter->formatCurrency((float) $row['amount'], 'GBP');
            }
            $variants[$name][] = [
                'amount' => $amount,
                'merchant' => $this->merchantsConfig[$row['code']]['name'],
            ];
        }

        $htmlTitle = $this->formatTitle($brand, $fragrance);

        $html = $this->twig->render('fragrance.twig', [
            'brand' => $brand,
            'fragrance' => $fragrance,
            'html_title' => $htmlTitle,
            'variants' => $variants,
        ]);

        return new Response($html);
    }

    private function formatTitle(BrandModel $brand, FragranceModel $fragrance): string
    {
        $title = sprintf('%s %s %s', $brand->getName(), $fragrance->getName(), $fragrance->getType());

        switch ($fragrance->getGender()) {
            case 'male':
                $title .= ' for men';
                break;
            case 'female':
                $title .= ' for women';
                break;
            default:
                $title .= ' unisex';
        }

        return $title;
    }
}
