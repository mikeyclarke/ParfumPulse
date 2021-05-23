<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use Doctrine\DBAL\Connection;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandStorage;
use ParfumPulse\Fragrance\FragranceStorage;

class BrandDeduplicator
{
    public function __construct(
        private BrandStorage $brandStorage,
        private Connection $connection,
        private FragranceStorage $fragranceStorage,
    ) {
    }

    public function deduplicate(BrandModel $originalBrand, BrandModel $duplicateBrand): void
    {
        $originalBrandId = $originalBrand->getId();
        $duplicateBrandId = $duplicateBrand->getId();

        $this->connection->beginTransaction();
        try {
            $this->moveFragrancesToOriginalBrand($originalBrandId, $duplicateBrandId);
            $this->deleteDuplicateBrand($duplicateBrandId);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function deleteDuplicateBrand(int $duplicateBrandId): void
    {
        $this->brandStorage->delete($duplicateBrandId);
    }

    private function moveFragrancesToOriginalBrand(int $originalBrandId, int $duplicateBrandId): void
    {
        $this->fragranceStorage->bulkUpdate(['brand_id' => $originalBrandId], ['brand_id' => $duplicateBrandId]);
    }
}
