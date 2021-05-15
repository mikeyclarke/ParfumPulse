<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use Doctrine\DBAL\Connection;
use PDO;

class ProductRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'url_path',
        'variant_id',
        'merchant_page_id',
        'merchant_id',
    ];
    private const TABLE_NAME = 'product';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findOneById(int $id, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('id = :id');

        $qb->setParameter('id', $id, PDO::PARAM_INT);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function findOneBy(array $criteria, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME);

        foreach ($criteria as $column => $val) {
            $qb->andWhere(sprintf('%s = :%s', $column, $column));
            $qb->setParameter($column, $val);
        }

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
