<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use Doctrine\DBAL\Connection;

class BrandRepository
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

    public function findOneById(int $id, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('id = :id');

        $qb->setParameter('id', $id);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function findOneByName(string $name, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('lower(unaccent(name)) = lower(unaccent(:name))');

        $qb->setParameter('name', $name);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function findOneByUrlSlug(string $urlSlug, array $additionalFields = []): ?array
    {
        $fields = array_merge(self::DEFAULT_FIELDS, $additionalFields);

        $qb = $this->connection->createQueryBuilder();

        $qb->select($fields)
            ->from(self::TABLE_NAME)
            ->where('url_slug = :url_slug');

        $qb->setParameter('url_slug', $urlSlug);

        $result = $qb->fetchAssociative();
        if (false === $result) {
            return null;
        }
        return $result;
    }

    public function getAll(): array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select(self::DEFAULT_FIELDS)
           ->from(self::TABLE_NAME)
           ->orderBy('name', 'ASC');

        $result = $qb->fetchAllAssociative();
        return $result;
    }

    public function countAll(): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::TABLE_NAME;

        $statementResult = $this->connection->executeQuery($sql);

        // @phpstan-ignore-next-line
        return $statementResult->fetchOne();
    }
}
