<?php

declare(strict_types=1);

namespace ParfumPulse\Merchant;

use Doctrine\DBAL\Connection;

class MerchantRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'code',
    ];
    private const TABLE_NAME = 'merchant';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function findOneByCode(string $code, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('code = :code');

        $qb->setParameter('code', $code);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
