<?php

declare(strict_types=1);

namespace ParfumPulse\Tests\Brand;

use Doctrine\DBAL\Connection;
use Mockery as m;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ParfumPulse\Brand\BrandDeduplicator;
use ParfumPulse\Brand\BrandModel;
use ParfumPulse\Brand\BrandStorage;
use ParfumPulse\Fragrance\FragranceStorage;

class BrandDeduplicatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private LegacyMockInterface $brandStorage;
    private LegacyMockInterface $connection;
    private LegacyMockInterface $fragranceStorage;
    private BrandDeduplicator $brandDeduplicator;

    public function setUp(): void
    {
        $this->brandStorage = m::mock(BrandStorage::class);
        $this->connection = m::mock(Connection::class);
        $this->fragranceStorage = m::mock(fragranceStorage::class);

        $this->brandDeduplicator = new BrandDeduplicator(
            $this->brandStorage,
            $this->connection,
            $this->fragranceStorage,
        );
    }

    public function testDeduplicate(): void
    {
        $originalBrandId = 123;
        $originalBrand = new BrandModel();
        $originalBrand->setId($originalBrandId);
        $duplicateBrandId = 456;
        $duplicateBrand = new BrandModel();
        $duplicateBrand->setId($duplicateBrandId);

        $this->createConnectionBeginTransactionExpectation();
        $this->createFragranceStorageExpectation([['brand_id' => $originalBrandId], ['brand_id' => $duplicateBrandId]]);
        $this->createBrandStorageExpectation([$duplicateBrandId]);
        $this->createConnectionCommitExpectation();

        $this->brandDeduplicator->deduplicate($originalBrand, $duplicateBrand);
    }

    private function createConnectionBeginTransactionExpectation(): void
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createFragranceStorageExpectation(array $args): void
    {
        $this->fragranceStorage
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with(...$args);
    }

    private function createBrandStorageExpectation(array $args): void
    {
        $this->brandStorage
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
