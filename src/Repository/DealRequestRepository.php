<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Vendeur;
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

    public function findOffersByVendeur(Vendeur $vendeur): array
    {
        return $this->createQueryBuilder('dr')

            // On récupère aussi la demande liée à l'offre.
            // Cela permet d'afficher le nom du produit dans Twig :
            // offre.idDemande.nomProduit
            ->leftJoin('dr.id_demande', 'd')
            ->addSelect('d')

            // On récupère aussi le client lié à l'offre.
            // Cela permet d'afficher le username du client dans Twig :
            // offre.clientUsername.username
            ->leftJoin('dr.client_username', 'c')
            ->addSelect('c')

            // On garde seulement les offres du vendeur connecté.
            ->andWhere('dr.vendeur_username = :vendeur')
            ->setParameter('vendeur', $vendeur)

            // On affiche les offres les plus récentes en premier.
            ->orderBy('dr.created_at', 'DESC')
            ->addOrderBy('dr.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
