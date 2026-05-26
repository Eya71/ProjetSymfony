<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CommandeRepository;
use App\Repository\ClientRepository;

final class MesCommandesController extends AbstractController
{
    #[Route('/mes-commandes', name: 'mes_commandes')]
    public function index(
        CommandeRepository $commandeRepository,
        ClientRepository $clientRepository
    ): Response {

        $user = $this->getUser();

        if (!$user) {

            return $this->redirectToRoute('app_login');

        }

        $client = $clientRepository->findOneBy([
            'username' => $user->getUserIdentifier()
        ]);

        $commandes = $commandeRepository->findBy(
            [
                'client' => $client,
                'source' => 'panier'
            ],
            [
                'createdAt' => 'DESC'
            ]
        );

        return $this->render(
            'mes_commandes/index.html.twig',
            [
                'commandes' => $commandes
            ]
        );
    }
}
