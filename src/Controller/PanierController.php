<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class PanierController extends AbstractController
{
    #[Route('/panier/add/{id}', name: 'panier_add')]
    public function add(
        Produit                $produit,
        EntityManagerInterface $entityManager
    ): Response
    {

        if (!$this->getUser()) {

            return $this->redirectToRoute('app_login');

        }
        //verifier si panier existe ou non deja
        $existingPanier =
            $entityManager
                ->getRepository(Panier::class)
                ->findOneBy([

                    'username' =>
                        $this->getUser()->getUserIdentifier(),

                    'id_produit' =>
                        $produit->getId()

                ]);
        if ($existingPanier) {

            $existingPanier->setQuantite(
                $existingPanier->getQuantite() + 1
            );

            $entityManager->flush();

            return $this->redirectToRoute(
                'panier_index'
            );

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
    public function index(
        PanierRepository  $panierRepository,
        ProduitRepository $produitRepository
    ): Response
    {


        if (!$this->getUser()) {

            return $this->redirectToRoute('app_login');

        }

        $username =
            $this->getUser()->getUserIdentifier();

        $paniers = $panierRepository->findBy([
            'username' => $username
        ]);

        $items = [];

        $total = 0;

        foreach ($paniers as $panier) {

            $produit =
                $produitRepository->find(
                    $panier->getIdProduit()
                );

            if ($produit) {

                $subtotal =
                    $produit->getPrix()
                    * $panier->getQuantite();

                $total += $subtotal;

                $items[] = [

                    'id' =>
                        $panier->getId(),

                    'nom_produit' =>
                        $produit->getNomProduit(),

                    'prix' =>
                        $produit->getPrix(),

                    'description' =>
                        $produit->getDecription(),

                    'categorie' =>
                        $produit->getCategorie(),

                    'image_path' =>
                        $produit->getImagePath(),

                    'quantite' =>
                        $panier->getQuantite()

                ];
            }
        }

        return $this->render(
            'panier/index.html.twig',
            [

                'items' => $items,

                'total' => $total

            ]
        );
    }
    //pour update du panier
    #[Route('/panier/update/{id}', name: 'panier_update')]
    public function update(
        Panier                 $panier,
        Request                $request,
        ProduitRepository      $produitRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        if (
            !$this->getUser()
            ||
            $panier->getUsername()
            !== $this->getUser()->getUserIdentifier()
        ) {

            return $this->redirectToRoute(
                'panier_index'
            );

        }
        $quantite =
            (int)$request->request->get(
                'quantite'
            );

        if ($quantite < 1) {

            $quantite = 1;

        }
        $produit =
            $produitRepository->find(
                $panier->getIdProduit()
            );

        if (!$produit) {

            return $this->redirectToRoute(
                'panier_index'
            );

        }
        if ($quantite > $produit->getQuantite()) {

            $this->addFlash(
                'error',
                'Stock insuffisant'
            );

            return $this->redirectToRoute(
                'panier_index'
            );
        }
        $panier->setQuantite($quantite);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Quantité mise à jour'
        );

        return $this->redirectToRoute(
            'panier_index'
        );

    }
    //pour suppression du panier
    #[Route('/panier/delete/{id}', name: 'panier_delete')]
    public function delete(
        Panier $panier,
        EntityManagerInterface $entityManager
    ): Response {
        if (
            !$this->getUser()
            ||
            $panier->getUsername()
            !== $this->getUser()->getUserIdentifier()
        ) {

            return $this->redirectToRoute(
                'panier_index'
            );

        }
        $entityManager->remove($panier);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Produit supprimé'
        );
        return $this->redirectToRoute(
            'panier_index'
        );
    }

}