<?php

declare(strict_types=1);

namespace OCA\Crate\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CrateShare>
 */
class CrateShareMapper extends QBMapper
{
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'crate_shares', CrateShare::class);
    }

    /** @return CrateShare[] Items this user has shared with others */
    public function findByOwnerAndShareable(string $ownerUserId, string $type, int $shareableId): array
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('owner_user_id', $qb->createNamedParameter($ownerUserId)))
            ->andWhere($qb->expr()->eq('shareable_type', $qb->createNamedParameter($type)))
            ->andWhere($qb->expr()->eq('shareable_id', $qb->createNamedParameter($shareableId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }

    /** @return CrateShare[] Items shared WITH this user */
    public function findSharedWithUser(string $userId): array
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($userId)))
            ->orderBy('created_at', 'DESC');
        return $this->findEntities($qb);
    }

    /** @throws DoesNotExistException */
    public function findByIdAndOwner(int $id, string $ownerUserId): CrateShare
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('owner_user_id', $qb->createNamedParameter($ownerUserId)));
        return $this->findEntity($qb);
    }

    public function alreadyShared(string $ownerUserId, string $sharedWithUserId, string $type, int $shareableId): bool
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('id')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('owner_user_id', $qb->createNamedParameter($ownerUserId)))
            ->andWhere($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($sharedWithUserId)))
            ->andWhere($qb->expr()->eq('shareable_type', $qb->createNamedParameter($type)))
            ->andWhere($qb->expr()->eq('shareable_id', $qb->createNamedParameter($shareableId, IQueryBuilder::PARAM_INT)));
        try {
            $this->findEntity($qb);
            return true;
        } catch (DoesNotExistException) {
            return false;
        }
    }

    public function deleteByShareable(string $type, int $shareableId): void
    {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
            ->where($qb->expr()->eq('shareable_type', $qb->createNamedParameter($type)))
            ->andWhere($qb->expr()->eq('shareable_id', $qb->createNamedParameter($shareableId, IQueryBuilder::PARAM_INT)));
        $qb->executeStatement();
    }
}
