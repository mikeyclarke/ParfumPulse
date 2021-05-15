<?php

declare(strict_types=1);

namespace ParfumPulse\Fragrance;

use Doctrine\DBAL\Connection;
use PDO;

class FragranceStorage
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

    public function insert(
        string $name,
        string $gender,
        string $type,
        string $urlId,
        string $urlSlug,
        int $brandId
    ): array {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    name,
    gender,
    type,
    url_id,
    url_slug,
    brand_id
)
VALUES
(
    :name,
    :gender,
    :type,
    :url_id,
    :url_slug,
    :brand_id
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('gender', $gender, PDO::PARAM_STR);
        $stmt->bindValue('type', $type, PDO::PARAM_STR);
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_STR);
        $stmt->bindValue('url_slug', $urlSlug, PDO::PARAM_STR);
        $stmt->bindValue('brand_id', $brandId, PDO::PARAM_INT);

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }
}
