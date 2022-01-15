<?php

declare(strict_types=1);

namespace ParfumPulse\FrontEnd\Controller;

use NumberFormatter;
use ParfumPulse\Brand\BrandRepository;
use ParfumPulse\Fragrance\FragranceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class HomePageController
{
    public function __construct(
        private BrandRepository $brandRepository,
        private FragranceRepository $fragranceRepository,
        private Twig $twig,
        private array $merchantsConfig,
    ) {
    }

    public function getAction(Request $request): Response
    {
        $numberFormatter = new NumberFormatter('en_GB', \NumberFormatter::DECIMAL);

        $platformStats = [
            'brands' => $numberFormatter->format($this->brandRepository->countAll()),
            'fragrances' => $numberFormatter->format($this->fragranceRepository->countAll()),
            'merchants' => $numberFormatter->format(count(array_keys($this->merchantsConfig))),
        ];

        $html = $this->twig->render('home.twig', [
            'platform_stats' => $platformStats,
        ]);

        return new Response($html);
    }
}
