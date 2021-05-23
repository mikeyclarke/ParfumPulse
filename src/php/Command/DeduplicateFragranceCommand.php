<?php

declare(strict_types=1);

namespace ParfumPulse\Command;

use ParfumPulse\Fragrance\FragranceDeduplicator;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeduplicateFragranceCommand extends Command
{
    protected static $defaultName = 'app:fragrances:deduplicate';

    public function __construct(
        private FragranceDeduplicator $fragranceDeduplicator,
        private FragranceRepository $fragranceRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Merge a duplicate fragrances into the original, re-assigning any variants belonging to the duplicate to ' .
            'the original and then deleting the duplicate.'
        );
        $this->addArgument('original-fragrance-id', InputArgument::REQUIRED, 'The ID of the fragrance to keep');
        $this->addArgument('duplicate-fragrance-id', InputArgument::REQUIRED, 'The ID of the fragrance to delete');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $originalFragranceId = $input->getArgument('original-fragrance-id');
        if (!is_numeric($originalFragranceId)) {
            $io->error('Argument original-fragrance-id should be an integer.');
            return Command::FAILURE;
        }

        $duplicateFragranceId = $input->getArgument('duplicate-fragrance-id');
        if (!is_numeric($duplicateFragranceId)) {
            $io->error('Argument duplicate-fragrance-id should be an integer.');
            return Command::FAILURE;
        }

        $originalFragrance = $this->getFragrance((int) $originalFragranceId);
        if (null === $originalFragrance) {
            $io->error('Fragrance does not exist with original-fragrance-id..');
            return Command::FAILURE;
        }

        $duplicateFragrance = $this->getFragrance((int) $duplicateFragranceId);
        if (null === $duplicateFragrance) {
            $io->error('Fragrance does not exist with duplicate-fragrance-id..');
            return Command::FAILURE;
        }

        $this->fragranceDeduplicator->deduplicate($originalFragrance, $duplicateFragrance);

        $io->success('Fragrances were successfully merged.');

        return Command::SUCCESS;
    }

    private function getFragrance(int $id): ?FragranceModel
    {
        $result = $this->fragranceRepository->findOneById($id);
        if (null === $result) {
            return null;
        }
        return FragranceModel::createFromArray($result);
    }
}
