<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use Doctrine\DBAL\Connection;

class FragranceRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'gender',
        'type',
        'url_id',
        'url_slug',
        'brand_id',
    ];
    private const TABLE_NAME = 'fragrance';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findOneBy(array $criteria, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME);

        foreach ($criteria as $column => $val) {
            if ($column === 'name') {
                $qb->andWhere('lower(unaccent(name)) = lower(unaccent(:name))');
            } else {
                $qb->andWhere(sprintf('%s = :%s', $column, $column));
            }
            $qb->setParameter($column, $val);
        }

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function findOneById(int $id, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->where('id = :id')
            ->from(self::TABLE_NAME);

        $qb->setParameter('id', $id);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function getAllForBrand(int $brandId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
           ->from(self::TABLE_NAME)
           ->where('brand_id = :brand_id')
           ->orderBy('name', 'ASC');

        $qb->setParameter('brand_id', $brandId);

        $result = $qb->fetchAllAssociative();
        return $result;
    }

    public function countAll(): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME;

        $statementResult = $this->connection->executeQuery($sql);

        // @phpstan-ignore-next-line
        return $statementResult->fetchOne();
    }
}
