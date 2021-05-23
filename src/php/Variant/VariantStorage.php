<?php

declare(strict_types=1);

namespace ParfumPulse\Variant;

use Doctrine\DBAL\Connection;
use PDO;

class VariantStorage
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

    public function insert(string $name, ?string $gtin, int $fragranceId): array
    {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    name,
    gtin,
    fragrance_id
)
VALUES
(
    :name,
    :gtin,
    :fragrance_id
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('gtin', $gtin, PDO::PARAM_STR);
        $stmt->bindValue('fragrance_id', $fragranceId, PDO::PARAM_INT);

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }

    public function bulkUpdate(array $parameters, array $criteria): void
    {
        $this->connection->update(self::TABLE_NAME, $parameters, $criteria);
    }
}
