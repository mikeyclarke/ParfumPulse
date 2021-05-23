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

    public function getFragranceVariantsData(int $fragranceId): array
    {
        $sql = <<<SQL
SELECT
    v.name,
    m.code,
    pr.amount
FROM variant v
LEFT JOIN product p ON p.variant_id = v.id
LEFT JOIN price pr ON pr.product_id = p.id
LEFT JOIN merchant m ON m.id = p.merchant_id
WHERE v.fragrance_id = :fragrance_id
AND pr.time_to IS NULL;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('fragrance_id', $fragranceId);
        $resultSet = $stmt->execute();
        return $resultSet->fetchAllAssociative();
    }

    public function getFragranceIdForGtins(array $gtins): ?int
    {
        $sql = 'SELECT fragrance_id FROM variant WHERE gtin IN (?) GROUP BY fragrance_id';

        $statementResult = $this->connection->executeQuery($sql, [$gtins], [Connection::PARAM_STR_ARRAY]);

        if ($statementResult->rowCount() > 1) {
            return null;
        }

        $result = $statementResult->fetchOne();
        if (false === $result) {
            return null;
        }

        if (!is_int($result)) {
            throw new \RuntimeException('fragrance_id is not an integer.');
        }

        return $result;
    }
}
