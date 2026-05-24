<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ProduitRepository $produitRepository): Response
    {

        $produits = $produitRepository->findAll();

        return $this->render('index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
