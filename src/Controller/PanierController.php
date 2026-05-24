<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier/add/{id}', name: 'panier_add')]
    public function add(
        Produit $produit,
        EntityManagerInterface $entityManager
    ): Response {


        if (!$this->getUser()) {

            return $this->redirectToRoute('app_login');

        }


        $panier = new Panier();

        $panier->setUsername(
            $this->getUser()->getUserIdentifier()
        );

        $panier->setIdProduit(
            $produit->getId()
        );

        $panier->setQuantite(1);

        $panier->setDateAjout(
            new \DateTimeImmutable()
        );



        $entityManager->persist($panier);

        $entityManager->flush();



        return $this->redirectToRoute(
            'panier_index'
        );

    }

    #[Route('/panier', name: 'panier_index')]
    public function index(): Response
    {
        return new Response('Panier');
    }
}