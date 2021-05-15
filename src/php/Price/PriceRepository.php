<?php

declare(strict_types=1);

namespace ParfumPulse\Price;

use Doctrine\DBAL\Connection;

class PriceRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'amount',
        'time_from',
        'time_to',
        'product_id',
    ];
    private const TABLE_NAME = 'price';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findCurrentPrice(int $productId, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->andWhere('time_to IS NULL')
            ->andWhere('product_id = :product_id');

        $qb->setParameter('product_id', $productId);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
