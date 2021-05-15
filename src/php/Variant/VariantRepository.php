<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

use Doctrine\DBAL\Connection;

class VariantRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'gtin',
        'fragrance_id',
    ];
    private const TABLE_NAME = 'variant';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findOneByName(string $name, int $fragranceId, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->andWhere('name = :name')
            ->andWhere('fragrance_id = :fragrance_id');

        $qb->setParameter('name', $name);
        $qb->setParameter('fragrance_id', $fragranceId);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function findOneByGtinOrName(
        string $gtin,
        string $name,
        int $fragranceId,
        array $additionalFields = []
    ): ?array {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('fragrance_id = :fragrance_id')
            ->andWhere(
                $qb->expr()->or(
                    $qb->expr()->eq('gtin', ':gtin'),
                    $qb->expr()->eq('name', ':name'),
                )
            );

        $qb->setParameter('fragrance_id', $fragranceId);
        $qb->setParameter('gtin', $gtin);
        $qb->setParameter('name', $name);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
