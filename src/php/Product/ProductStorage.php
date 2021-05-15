<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use Doctrine\DBAL\Connection;
use PDO;

class ProductStorage
{
    private const DEFAULT_FIELDS = [
        'id',
        'url_path',
        'variant_id',
        'merchant_page_id',
        'merchant_id',
    ];
    private const TABLE_NAME = 'product';
    private const UPDATE_FIELDS_GREENLIST = [
        'url_path',
        'free_delivery',
    ];

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function insert(
        string $urlPath,
        int $variantId,
        int $merchantPageId,
        int $merchantId,
        bool $freeDelivery = false
    ): array {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    url_path,
    variant_id,
    merchant_page_id,
    merchant_id,
    free_delivery
)
VALUES
(
    :url_path,
    :variant_id,
    :merchant_page_id,
    :merchant_id,
    :free_delivery
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('url_path', $urlPath, PDO::PARAM_STR);
        $stmt->bindValue('variant_id', $variantId, PDO::PARAM_INT);
        $stmt->bindValue('merchant_page_id', $merchantPageId, PDO::PARAM_INT);
        $stmt->bindValue('merchant_id', $merchantId, PDO::PARAM_INT);
        $stmt->bindValue('free_delivery', $freeDelivery, PDO::PARAM_BOOL);

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }

    public function update(int $id, array $parameters): array
    {
        $fieldsToUpdate = array_intersect_key($parameters, array_flip(self::UPDATE_FIELDS_GREENLIST));
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = sprintf('UPDATE %s SET ', self::TABLE_NAME);
        $sql .= implode(
            ', ',
            array_map(
                function ($field) {
                    return sprintf('%s = :%s', $field, $field);
                },
                array_keys($fieldsToUpdate)
            )
        );
        $sql .= ' WHERE id = :id';
        $sql .= " RETURNING {$fieldsToReturn}";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        foreach ($fieldsToUpdate as $name => $value) {
            if ($name === 'free_delivery') {
                $stmt->bindValue($name, $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($name, $value);
            }
        }

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }
}
