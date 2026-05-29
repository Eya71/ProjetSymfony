<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function countUnreadForRecipient(string $username, string $role): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.recipient_username = :u')
            ->andWhere('n.recipient_role = :r')
            ->andWhere('n.is_read = false')
            ->setParameter('u', $username)
            ->setParameter('r', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Notification[]
     */
    public function findFeedForRecipient(string $username, string $role, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.recipient_username = :u')
            ->andWhere('n.recipient_role = :r')
            ->setParameter('u', $username)
            ->setParameter('r', $role)
            ->orderBy('n.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function markOneReadFor(int $id, string $username, string $role): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.is_read', ':read')
            ->andWhere('n.id = :id')
            ->andWhere('n.recipient_username = :u')
            ->andWhere('n.recipient_role = :r')
            ->setParameter('read', true)
            ->setParameter('id', $id)
            ->setParameter('u', $username)
            ->setParameter('r', $role)
            ->getQuery()
            ->execute();
    }

    public function markMessageNotificationsRead(string $username, string $role, int $dealId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.is_read', ':read')
            ->andWhere('n.recipient_username = :u')
            ->andWhere('n.recipient_role = :r')
            ->andWhere('n.type = :type')
            ->andWhere('n.related_id = :deal')
            ->andWhere('n.is_read = false')
            ->setParameter('read', true)
            ->setParameter('u', $username)
            ->setParameter('r', $role)
            ->setParameter('type', 'new_message')
            ->setParameter('deal', $dealId)
            ->getQuery()
            ->execute();
    }

    public function markAllReadFor(string $username, string $role): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.is_read', ':read')
            ->andWhere('n.recipient_username = :u')
            ->andWhere('n.recipient_role = :r')
            ->andWhere('n.is_read = false')
            ->setParameter('read', true)
            ->setParameter('u', $username)
            ->setParameter('r', $role)
            ->getQuery()
            ->execute();
    }
}
