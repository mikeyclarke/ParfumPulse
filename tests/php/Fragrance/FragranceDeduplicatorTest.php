<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Fragrance;

use Doctrine\DBAL\Connection;
use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Fragrance\FragranceDeduplicator;
use ParfumPulse\Fragrance\FragranceModel;
use ParfumPulse\Fragrance\FragranceStorage;
use ParfumPulse\Variant\VariantStorage;

class FragranceDeduplicatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $connection;
    private LegacyMockInterface $fragranceStorage;
    private LegacyMockInterface $variantStorage;
    private FragranceDeduplicator $fragranceDeduplicator;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->fragranceStorage = m::mock(FragranceStorage::class);
        $this->variantStorage = m::mock(VariantStorage::class);

        $this->fragranceDeduplicator = new FragranceDeduplicator(
            $this->connection,
            $this->fragranceStorage,
            $this->variantStorage,
        );
    }

    public function testDeduplicate(): void
    {
        $originalFragranceId = 123;
        $originalFragrance = new FragranceModel();
        $originalFragrance->setId($originalFragranceId);
        $duplicateFragranceId = 456;
        $duplicateFragrance = new FragranceModel();
        $duplicateFragrance->setId($duplicateFragranceId);

        $this->createConnectionBeginTransactionExpectation();
        $this->createVariantStorageExpectation(
            [['fragrance_id' => $originalFragranceId], ['fragrance_id' => $duplicateFragranceId]]
        );
        $this->createFragranceStorageExpectation([$duplicateFragranceId]);
        $this->createConnectionCommitExpectation();

        $this->fragranceDeduplicator->deduplicate($originalFragrance, $duplicateFragrance);
    }

    private function createConnectionBeginTransactionExpectation(): void
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createVariantStorageExpectation(array $args): void
    {
        $this->variantStorage
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with(...$args);
    }

    private function createFragranceStorageExpectation(array $args): void
    {
        $this->fragranceStorage
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }

    private function createConnectionCommitExpectation(): void
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }
}
