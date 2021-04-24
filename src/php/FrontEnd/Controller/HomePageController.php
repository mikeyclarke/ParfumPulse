<?php

declare(strict_types=1);

namespace ParfumPulse\FrontEnd\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class HomePageController
{
    public function __construct(
        private Twig $twig,
    ) {
    }

    public function getAction(Request $request): Response
    {
        $html = $this->twig->render('home.twig');

        return new Response($html);
    }
}
