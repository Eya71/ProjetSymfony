<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\DealRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DealRequest>
 */
class DealRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DealRequest::class);
    }

    public function countUnseenForClient(Client $client): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.client_username = :client')
            ->andWhere('d.client_seen_at IS NULL OR d.created_at > d.client_seen_at')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
