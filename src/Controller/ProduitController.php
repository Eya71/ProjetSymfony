<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/produits', name: 'produit_index')]
    public function index(
        ProduitRepository $produitRepository
    ): Response {

        $produits = $produitRepository->findAll();

        return $this->render(
            'index.html.twig',
            [
                'produits' => $produits
            ]
        );
    }

    #[Route('/produit/{id}', name: 'produit_details')]
    public function details(
        int $id,
        ProduitRepository $produitRepository
    ): Response {

        $produit = $produitRepository->find($id);

        if (!$produit) {

            throw $this->createNotFoundException(
                'Produit introuvable'
            );

        }
        $produitsSimilaires = $produitRepository->findBy(
            [
                'categorie' => $produit->getCategorie()
            ]
        );
        $produitsSimilaires = array_filter(
            $produitsSimilaires,
            function ($p) use ($produit) {

                return $p->getId() !== $produit->getId();

            }
        );
        $produitsSimilaires = array_slice(
            $produitsSimilaires,
            0,
            4
        );
        return $this->render(
            'produit/details.html.twig',
            [
                'produit' => $produit,
                'produits_similaires' => $produitsSimilaires
            ]
        );
    }
}