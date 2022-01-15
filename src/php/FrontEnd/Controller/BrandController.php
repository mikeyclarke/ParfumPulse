<?php

declare(strict_types=1);

namespace ParfumPulse\FrontEnd\Controller;

use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;
use ParfumPulse\Fragrance\FragranceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class BrandController
{
    public function __construct(
        private BrandRepository $brandRepository,
        private FragranceRepository $fragranceRepository,
        private Twig $twig,
    ) {
    }

    public function getAction(Request $request): Response
    {
        $urlSlug = $request->attributes->get('brand_url_slug');

        $result = $this->brandRepository->findOneByUrlSlug($urlSlug);
        if (null === $result) {
            throw new NotFoundHttpException();
        }
        $brand = BrandModel::createFromArray($result);

        $fragrances = $this->fragranceRepository->getAllForBrand($brand->getId());

        $html = $this->twig->render('brand.twig', [
            'brand' => $brand,
            'fragrances' => $fragrances,
            'html_title' => sprintf('%s fragrances', $brand->getName()),
        ]);

        return new Response($html);
    }
}
