<?php

declare(strict_types=1);

namespace ParfumPulse\Command;

use ParfumPulse\Command\Exception\ElevatedErrorLevelsException;
use ParfumPulse\Merchant\Exception\NoMerchantWithCodeException;
use ParfumPulse\Merchant\MerchantLazyCreation;
use ParfumPulse\Merchant\MerchantModel;
use ParfumPulse\Scraper\Exception\ScraperException;
use ParfumPulse\Scraper\ScraperFactory;
use ParfumPulse\Scraper\ScraperFailuresProcessor;
use ParfumPulse\Scraper\ScraperInterface;
use ParfumPulse\Scraper\ScraperResultsProcessor;
use ParfumPulse\Scraper\UrlCollection;
use ParfumPulse\Scraper\UrlCollectionBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScraperCommand extends Command
{
    protected static $defaultName = 'app:scrape';

    private const ABORT_THRESHOLD = 0.75;
    private const CHECKPOINT_FREQUENCY = 30;
    private const SCRAPE_DELAY = 2;

    public function __construct(
        private MerchantLazyCreation $merchantLazyCreation,
        private ScraperFactory $scraperFactory,
        private ScraperFailuresProcessor $scraperFailuresProcessor,
        private ScraperResultsProcessor $scraperResultsProcessor,
        private UrlCollectionBuilder $urlCollectionBuilder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Scrape merchant websites for new and updated fragrance prices');
        $this->addArgument('merchant-code', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $merchantCode = $input->getArgument('merchant-code');
        if (!is_string($merchantCode)) {
            $io->error('Argument merchant-code should be a string.');
            return Command::FAILURE;
        }

        try {
            $merchant = $this->merchantLazyCreation->createOrRetrieve($merchantCode);
        } catch (NoMerchantWithCodeException) {
            $io->error('Unsupported merchant.');
            return Command::FAILURE;
        }

        $urlCollection = $this->urlCollectionBuilder->build($merchant);
        $scraper = $this->scraperFactory->create($merchant);

        $io->section('Running initial scraping');

        $failures = $this->runScraper($io, $scraper, $merchant, $urlCollection);
        if (empty($failures)) {
            $io->success(sprintf('%d pages sucessfully scraped.', count($urlCollection)));
            return Command::SUCCESS;
        }

        $io->caution(sprintf(
            '%d pages were successfully scraped, but %d failed',
            count($urlCollection) - count($failures),
            count($failures)
        ));

        $retries = new UrlCollection();
        foreach ($failures as $url => $props) {
            $retries->add($url, $props['lastmod'], $props['productIds']);
        }

        $io->section('Retrying failed requests');

        $retryFailures = $this->runScraper($io, $scraper, $merchant, $retries);
        if (empty($retryFailures)) {
            $io->success(sprintf('%d pages sucessfully scraped.', count($retries)));
            return Command::SUCCESS;
        }

        $io->caution(sprintf(
            '%d retries succeeded, but %d failed',
            count($retries) - count($retryFailures),
            count($retryFailures)
        ));

        $io->info('Failures:');
        $io->listing(array_keys($retryFailures));

        $this->scraperFailuresProcessor->process($merchant, $retryFailures);

        return Command::SUCCESS;
    }

    private function runScraper(
        SymfonyStyle $io,
        ScraperInterface $scraper,
        MerchantModel $merchant,
        UrlCollection $urlCollection
    ): array {
        $failures = [];

        $urlsToScrape = count($urlCollection);
        $io->progressStart($urlsToScrape);

        $stageResults = [];
        $stageFailureCount = 0;
        $i = 0;
        $lastCheckpoint = 0;
        foreach ($urlCollection as $url => $props) {
            if ($i > 0) {
                sleep(self::SCRAPE_DELAY);
            }

            try {
                $stageResults[] = $scraper->scrape($url, $props);
            } catch (ScraperException) {
                $stageFailureCount += 1;
                $failures[$url] = $props;
            }

            $i += 1;

            if (0 === $i % self::CHECKPOINT_FREQUENCY || $i === $urlsToScrape) {
                $this->scraperResultsProcessor->process($merchant, $stageResults);
                $stageResults = [];

                if ($stageFailureCount / self::CHECKPOINT_FREQUENCY >= self::ABORT_THRESHOLD) {
                    throw new ElevatedErrorLevelsException();
                }
                $stageFailureCount = 0;

                $io->progressAdvance($i - $lastCheckpoint);

                $lastCheckpoint = $i;
            }
        }

        $io->progressFinish();

        return $failures;
    }
}
