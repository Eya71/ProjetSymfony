<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeRepository;
use Doctrine\DBAL\Connection;
use App\Entity\Demande;
 use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;

use App\Repository\DealRequestRepository;

final class     MesdemandesController extends AbstractController

{#[Route('/mes-demandes', name: 'mes_demandes')]
public function mesDemandes(
    DemandeRepository $demandeRepository,
    ClientRepository $clientRepository
): Response {

    if (!$this->getUser()) {

        return $this->redirectToRoute('app_login');

    }

    $client = $clientRepository->findOneBy([
        'username' => $this->getUser()->getUsername()
    ]);

    $demandes = $demandeRepository->findBy(
        [
            'username' => $client
        ],
        [
            'id' => 'DESC'
        ]
    );

    return $this->render(
        'demandes/mes_demandes.html.twig',
        [
            'demandes' => $demandes
        ]
    );

}

#[Route('/demande/{id}', name: 'demande_details')]
public function details(
    Demande $demande,
    CommandeRepository $commandeRepository
): Response {

    $commande = $commandeRepository->findOneBy([
        'id_demande' => $demande
    ]);

    return $this->render(
        'demandes/details.html.twig',
        [
            'demande' => $demande,
            'commande' => $commande
        ]
    );

}


#[Route('/demande/delete/{id}', name: 'demande_delete')]
public function delete(
    Demande $demande,
    EntityManagerInterface $entityManager
): Response {

    $entityManager->remove($demande);

    $entityManager->flush();

    $this->addFlash(
        'success',
        'Demande supprimée avec succès.'
    );

    return $this->redirectToRoute('mes_demandes');

}

#[Route('/demande/{id}/offres', name: 'demande_offres')]
public function offres(
    int $id,
    DealRequestRepository $dealRequestRepository
): Response {

    $offres = $dealRequestRepository->findBy(
        [
            'id_demande' => $id
        ],
        [
            'prix_propose' => 'ASC'
        ]
    );

    return $this->render(
        'offre/offres.html.twig',
        [
            'offres' => $offres
        ]
    );

}



}
