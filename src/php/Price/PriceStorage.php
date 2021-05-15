<?php

declare(strict_types=1);

namespace ParfumPulse\Price;

use Doctrine\DBAL\Connection;
use PDO;

class PriceStorage
{
    private const DEFAULT_FIELDS = [
        'id',
        'amount',
        'time_from',
        'time_to',
        'product_id',
    ];
    private const TABLE_NAME = 'price';
    private const UPDATE_FIELDS_GREENLIST = [
        'time_to',
    ];

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function insert(int $productId, float $amount, string $timeFrom): array
    {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    amount,
    time_from,
    product_id
)
VALUES
(
    :amount,
    :time_from,
    :product_id
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('amount', $amount);
        $stmt->bindValue('time_from', $timeFrom, PDO::PARAM_STR);
        $stmt->bindValue('product_id', $productId, PDO::PARAM_INT);

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
            $stmt->bindValue($name, $value);
        }

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }
}
