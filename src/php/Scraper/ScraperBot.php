<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

use ParfumPulse\Scraper\Exception\NonSuccessfulResponseException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class ScraperBot
{
    private const USER_AGENT = 'Mozilla/5.0 (compatible; ParfumBot/0.1)';

    private HttpBrowser $browser;

    public function __construct(
        private string $baseUrl,
    ) {
        $this->browser = new HttpBrowser(HttpClient::create());
        $this->browser->setServerParameter('HTTP_USER_AGENT', self::USER_AGENT);
    }

    public function createRequest(string $url): void
    {
        $this->resetHistory();

        $this->browser->request('GET', sprintf('%s%s', $this->baseUrl, $url));
        $response = $this->browser->getResponse();
        // @phpstan-ignore-next-line
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new NonSuccessfulResponseException();
        }
    }

    public function getStructuredProductData(): ?array
    {
        $crawler = $this->browser->getCrawler();
        $dataNodes = $crawler->filter('script[type="application/ld+json"]');
        foreach ($dataNodes as $node) {
            $text = $node->textContent;
            $json = json_decode($text, true);
            if (is_array($json) && isset($json['@type']) && strtolower($json['@type']) === 'product') {
                return $json;
            }
        }
        return null;
    }

    public function getApolloStateData(): ?array
    {
        $crawler = $this->browser->getCrawler();
        $dataNodes = $crawler->filter('script#__APOLLO_STATE__');
        foreach ($dataNodes as $node) {
            $text = $node->textContent;
            $json = json_decode($text, true);
            if (is_array($json)) {
                return $json;
            }
        }
        return null;
    }

    private function resetHistory(): void
    {
        $this->browser->getHistory()->clear();
    }
}
