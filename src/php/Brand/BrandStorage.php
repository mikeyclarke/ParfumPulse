<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use Doctrine\DBAL\Connection;
use PDO;

class BrandStorage
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

    public function insert(string $name, string $urlSlug): array
    {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    name,
    url_slug
)
VALUES
(
    :name,
    :url_slug
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('name', $name, PDO::PARAM_STR);
        $stmt->bindValue('url_slug', $urlSlug, PDO::PARAM_STR);

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }

    public function delete(int $id): void
    {
        $this->connection->delete(self::TABLE_NAME, ['id' => $id]);
    }
}
