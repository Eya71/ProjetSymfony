<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeRepository;

final class MesdemandesController extends AbstractController
{
    #[Route('/mes-demandes', name: 'mes_demandes')]
    public function mesDemandes(
        DemandeRepository $demandeRepository
    ): Response {
        if (!$this->getUser()) {

            return $this->redirectToRoute(
                'app_login'
            );

        }
        $demandes =
            $demandeRepository->findBy(
                [
                    'username' => $this->getUser()->getUserIdentifier()
                ],
                [
                    'id' => 'DESC'
                ]
            );

        return $this->render(
            'demandes/mes-demandes.html.twig',
            [
                'demandes' => $demandes
            ]
        );

    }
    #[Route('/demande/{id}', name: 'demande_details')]
    public function details(
        Demande $demande
    ): Response {

        return $this->render(
            'demandes/details.html.twig',
            [
                'demande' => $demande
            ]
        );

    }
}
