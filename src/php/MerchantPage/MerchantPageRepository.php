<?php

declare(strict_types=1);

namespace ParfumPulse\MerchantPage;

use Doctrine\DBAL\Connection;

class MerchantPageRepository
{
    private const DEFAULT_FIELDS = [
        'id',
        'url_path',
        'should_scrape',
        'failed_scrape_days',
        'merchant_id',
    ];
    private const TABLE_NAME = 'merchant_page';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function getAllUrlsAndProductIds(int $merchantId): array
    {
        $sql = <<<SQL
SELECT page.url_path, page.failed_scrape_days, product.id AS product_id
FROM merchant_page page
LEFT JOIN product product ON product.merchant_page_id = page.id
WHERE page.merchant_id = :merchant_id AND page.should_scrape IS TRUE
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('merchant_id', $merchantId);
        $resultSet = $stmt->execute();
        return $resultSet->fetchAllAssociative();
    }

    public function findOneByUrlPath(string $urlPath, int $merchantId, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->andWhere('url_path = :url_path')
            ->andWhere('merchant_id = :merchant_id');

        $qb->setParameter('url_path', $urlPath);
        $qb->setParameter('merchant_id', $merchantId);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }
}
