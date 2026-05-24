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
        // Récupère tous les produits depuis la base via Doctrine
        // Vous pouvez remplacer findAll() par une méthode personnalisée (par ex. findVisible, pagination, tri)
        $produits = $produitRepository->findAll();

        // On passe les entités Produit à Twig — Twig utilisera les getters de l'entité (ex: prod.getNomProduit)
        return $this->render('index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
