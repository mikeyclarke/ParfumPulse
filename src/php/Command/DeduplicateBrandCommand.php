<?php

declare(strict_types=1);

namespace ParfumPulse\Command;

use ParfumPulse\Brand\BrandDeduplicator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeduplicateBrandCommand extends Command
{
    protected static $defaultName = 'app:brands:deduplicate';

    public function __construct(
        private BrandDeduplicator $brandDeduplicator,
        private BrandRepository $brandRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Merge a duplicate brand into the original, re-assigning any fragrances belonging to the duplicate to ' .
            'the original and then deleting the duplicate.'
        );
        $this->addArgument('original-brand-id', InputArgument::REQUIRED, 'The ID of the brand to keep');
        $this->addArgument('duplicate-brand-id', InputArgument::REQUIRED, 'The ID of the brand to delete');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $originalBrandId = $input->getArgument('original-brand-id');
        if (!is_numeric($originalBrandId)) {
            $io->error('Argument original-brand-id should be an integer.');
            return Command::FAILURE;
        }

        $duplicateBrandId = $input->getArgument('duplicate-brand-id');
        if (!is_numeric($duplicateBrandId)) {
            $io->error('Argument duplicate-brand-id should be an integer.');
            return Command::FAILURE;
        }

        $originalBrand = $this->getBrand((int) $originalBrandId);
        if (null === $originalBrand) {
            $io->error('Brand does not exist with original-brand-id..');
            return Command::FAILURE;
        }

        $duplicateBrand = $this->getBrand((int) $duplicateBrandId);
        if (null === $duplicateBrand) {
            $io->error('Brand does not exist with duplicate-brand-id..');
            return Command::FAILURE;
        }

        $this->brandDeduplicator->deduplicate($originalBrand, $duplicateBrand);

        $io->success('Brands were successfully merged.');

        return Command::SUCCESS;
    }

    private function getBrand(int $id): ?BrandModel
    {
        $result = $this->brandRepository->findOneById($id);
        if (null === $result) {
            return null;
        }
        return BrandModel::createFromArray($result);
    }
}
