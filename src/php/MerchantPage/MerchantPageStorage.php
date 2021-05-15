<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use Doctrine\DBAL\Connection;
use PDO;

class MerchantPageStorage
{
    private const DEFAULT_FIELDS = [
        'id',
        'url_path',
        'should_scrape',
        'failed_scrape_days',
        'merchant_id',
    ];
    private const TABLE_NAME = 'merchant_page';
    private const UPDATE_FIELDS_GREENLIST = [
        'should_scrape',
        'failed_scrape_days',
    ];

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function insert(
        string $urlPath,
        int $merchantId,
        bool $shouldScrape = true,
        int $failedScrapeDays = 0
    ): array {
        $tableName = self::TABLE_NAME;
        $fieldsToReturn = implode(', ', self::DEFAULT_FIELDS);

        $sql = <<<SQL
INSERT INTO $tableName
(
    url_path,
    merchant_id,
    should_scrape,
    failed_scrape_days
)
VALUES
(
    :url_path,
    :merchant_id,
    :should_scrape,
    :failed_scrape_days
)
RETURNING $fieldsToReturn
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('url_path', $urlPath, PDO::PARAM_STR);
        $stmt->bindValue('merchant_id', $merchantId, PDO::PARAM_INT);
        $stmt->bindValue('should_scrape', $shouldScrape, PDO::PARAM_BOOL);
        $stmt->bindValue('failed_scrape_days', $failedScrapeDays, PDO::PARAM_INT);

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
