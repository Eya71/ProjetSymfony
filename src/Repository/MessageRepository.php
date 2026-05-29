<?php

namespace App\Repository;

use App\Entity\DealRequest;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function countUnreadForReceiver(string $username): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.receiver_username = :username')
            ->andWhere('m.is_read = false')
            ->setParameter('username', $username)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Messages d'une conversation plus récents qu'un id donné (ordre chronologique).
     *
     * @return Message[]
     */
    public function findNewerThan(DealRequest $deal, int $sinceId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id_deal = :deal')
            ->andWhere('m.id > :since')
            ->setParameter('deal', $deal)
            ->setParameter('since', $sinceId)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastByDeal(DealRequest $deal): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id_deal = :deal')
            ->setParameter('deal', $deal)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countUnreadForDealAndReceiver(DealRequest $deal, string $username): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.id_deal = :deal')
            ->andWhere('m.receiver_username = :u')
            ->andWhere('m.is_read = false')
            ->setParameter('deal', $deal)
            ->setParameter('u', $username)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markReadForDealAndReceiver(DealRequest $deal, string $username): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.is_read', ':read')
            ->andWhere('m.id_deal = :deal')
            ->andWhere('m.receiver_username = :u')
            ->andWhere('m.is_read = false')
            ->setParameter('read', true)
            ->setParameter('deal', $deal)
            ->setParameter('u', $username)
            ->getQuery()
            ->execute();
    }
}
