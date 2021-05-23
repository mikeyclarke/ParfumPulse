<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use Doctrine\DBAL\Connection;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceStorage;
use ParfumPulse\Variant\VariantStorage;

class FragranceDeduplicator
{
    public function __construct(
        private Connection $connection,
        private FragranceStorage $fragranceStorage,
        private VariantStorage $variantStorage,
    ) {
    }

    public function deduplicate(FragranceModel $originalFragrance, FragranceModel $duplicateFragrance): void
    {
        $originalFragranceId = $originalFragrance->getId();
        $duplicateFragranceId = $duplicateFragrance->getId();

        $this->connection->beginTransaction();
        try {
            $this->moveVariantsToOriginalFragrance($originalFragranceId, $duplicateFragranceId);
            $this->deleteDuplicateFragrance($duplicateFragranceId);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function deleteDuplicateFragrance(int $duplicateFragranceId): void
    {
        $this->fragranceStorage->delete($duplicateFragranceId);
    }

    private function moveVariantsToOriginalFragrance(int $originalFragranceId, int $duplicateFragranceId): void
    {
        $this->variantStorage->bulkUpdate(
            ['fragrance_id' => $originalFragranceId],
            ['fragrance_id' => $duplicateFragranceId]
        );
    }
}
