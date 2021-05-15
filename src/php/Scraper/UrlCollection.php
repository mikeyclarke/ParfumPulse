<?php

declare(strict_types=1);

namespace ParfumPulse\Scraper;

class UrlCollection implements \IteratorAggregate, \Countable
{
    private array $urls = [];

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    public function count(): int
    {
        return count($this->urls);
    }

    public function add(
        string $url,
        string $lastmod = null,
        array $productIds = [],
        bool $hasFailedScrapeDays = false
    ): void {
        $this->urls[$url] = [
            'lastmod' => $lastmod,
            'productIds' => $productIds,
            'hasFailedScrapeDays' => $hasFailedScrapeDays,
        ];
    }

    public function get(string $url): ?array
    {
        return $this->urls[$url] ?? null;
    }

    public function remove(string|array $urlsToExclude): void
    {
        foreach ((array) $urlsToExclude as $url) {
            unset($this->urls[$url]);
        }
    }

    public function filter(callable $callback): void
    {
        foreach ($this->urls as $url => $props) {
            if (false === (bool) $callback($url, $props)) {
                unset($this->urls[$url]);
            }
        }
    }

    public function keys(): array
    {
        return array_keys($this->urls);
    }

    public function all(): array
    {
        return $this->urls;
    }

    public function addCollection(UrlCollection $collection): void
    {
        foreach ($collection->all() as $url => $props) {
            $this->urls[$url] = $props;
        }
    }
}
