<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\FrontEnd\Controller;

use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\FrontEnd\Controller\HomePageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class HomePageControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $twig;
    private HomePageController $controller;

    public function setUp(): void
    {
        $this->twig = m::mock(Twig::class);

        $this->controller = new HomePageController(
            $this->twig,
        );
    }

    public function testGetAction(): void
    {
        $request = new Request();

        $html = '<h1>Welcome to ParfumPulse.</h1>';

        $this->createTwigExpectation(['home.twig'], $html);

        $result = $this->controller->getAction($request);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals($html, $result->getContent());
    }

    private function createTwigExpectation(array $args, string $result): void
    {
        $this->twig
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
