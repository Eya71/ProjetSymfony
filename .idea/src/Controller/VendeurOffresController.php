<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VendeurOffresController extends AbstractController
{
    #[Route('/vendeur/offres', name: 'app_vendeur_offres')]
    public function index(): Response
    {
        return $this->render('vendeur_offres/index.html.twig', [
            'controller_name' => 'VendeurOffresController',
        ]);
    }
}
