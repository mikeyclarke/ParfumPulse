<?php

declare(strict_types=1);

namespace ParfumPulse\Merchant;

use Doctrine\DBAL\Connection;
use PDO;

class MerchantStorage
{
    private const TABLE_NAME = 'merchant';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function insert(string $code): array
    {
        $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (code) VALUES (:code) RETURNING id, code';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('code', $code, PDO::PARAM_STR);

        $statementResult = $stmt->execute();
        // @phpstan-ignore-next-line
        return $statementResult->fetchAssociative();
    }
}
