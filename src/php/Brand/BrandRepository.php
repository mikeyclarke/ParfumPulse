<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use Doctrine\DBAL\Connection;

class BrandRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'name',
        'url_slug',
    ];
    private const TABLE_NAME = 'brand';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findOneByName(string $name, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('name = :name');

        $qb->setParameter('name', $name);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
