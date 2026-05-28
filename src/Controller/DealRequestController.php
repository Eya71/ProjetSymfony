<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DealRequest;
use App\Repository\DealRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DealRequestController extends AbstractController
{


    #[Route('/demande/{id}/offres', name: 'demande_offres'
    )]
    public function offres(
        int $id,
        DealRequestRepository $dealRequestRepository
    ): Response {

        $offres = $dealRequestRepository->findBy(
            [
                'idDemande' => $id
            ],
            [
                'prixPropose' => 'ASC'
            ]
        );

        return $this->render(
            'demandes/offres.html.twig',
            [
                'offres' => $offres
            ]
        );

    }
    #[Route('/offre/accepter', name: 'accepter_offre', methods: ['POST']
    )]
    public function accepterOffre(
        Request $request,
        DealRequestRepository $dealRequestRepository,
        EntityManagerInterface $entityManager
    ): Response {

        $id = $request
            ->request
            ->get('id');

        $offre = $dealRequestRepository
            ->find($id);

        if (!$offre) {

            throw $this->createNotFoundException(
                'Offre introuvable'
            );

        }
        $demande = $offre->getIdDemande();

        $commande = new Commande();

        $commande->setClient(
            $offre->getClientUsername()
        );

        $commande->setVendeur(
            $offre->getVendeurUsername()
        );

        $commande->setIdDemande($demande);



        $commande->setStatut('en cours');

        $commande->setSource('deal');

        $commande->setTotal(
            $offre->getPrixPropose()
        );

        $commande->setCreatedAt(
            new \DateTimeImmutable()
        );

        $entityManager->persist($commande);

        $offre->setStatus('accepted');

        if ($demande) { $demande->setEtat('en cours'); }


        $autresOffres = $dealRequestRepository->findBy([
            'id_demande' => $offre->getIdDemande()
        ]);



        foreach ($autresOffres as $autreOffre) {

            if (
                $autreOffre->getId()
                !==
                $offre->getId()
            ) {

                $entityManager->remove( $autreOffre );

            }

        }

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Offre acceptée avec succès.'
        );

        return $this->redirectToRoute(
            'mes_commandes'
        );

    }

}

