<?php

declare(strict_types=1);

namespace ParfumPulse\FrontEnd\Controller;

use ParfumPulse\Brand\BrandRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class HomePageController
{
    public function __construct(
        private BrandRepository $brandRepository,
        private Twig $twig,
    ) {
    }

    public function getAction(Request $request): Response
    {
        $brands = $this->brandRepository->getAll();

        $html = $this->twig->render('home.twig', [
            'brands' => $brands,
        ]);

        return new Response($html);
    }
}
