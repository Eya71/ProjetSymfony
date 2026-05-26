<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use App\Service\NotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitController extends AbstractController
{
    #[Route('/produit/add', name: 'produit_add')]
    public function add(LegacyImagePathResolver $imagePathResolver, Connection $connection): Response
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'vendeur') {
            return $this->redirectToRoute('app_login');
        }

        $userInfo = $connection->fetchAssociative(
            'SELECT idphoto FROM vendeur WHERE username = :u',
            ['u' => $user->getUsername()]
        ) ?: [];

        return $this->render('produit/add.html.twig', [
            'username' => $user->getUsername(),
            'role' => $user->getLegacyRole(),
            'photoUrl' => $imagePathResolver->profile($userInfo['idphoto'] ?? ''),
        ]);
    }

    #[Route('/produit/store', name: 'produit_store', methods: ['POST'])]
    public function store(Request $request, Connection $connection, NotificationService $notif): Response
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'vendeur') {
            return $this->redirectToRoute('app_login');
        }

        $nomProduit = trim((string) $request->request->get('nom_produit', ''));
        $prix = (string) $request->request->get('prix', '');
        $quantite = (int) $request->request->get('quantite', 1);
        $categorie = trim((string) $request->request->get('categorie', ''));
        $description = trim((string) $request->request->get('description', ''));
        $image = $request->files->get('image');

        if ($nomProduit === '' || $prix === '' || $categorie === '' || !$image) {
            $this->addFlash('error', 'Veuillez remplir tous les champs et joindre une image.');
            return $this->redirectToRoute('produit_add');
        }

        $newFilename = uniqid().'.'.$image->guessExtension();
        try {
            $image->move(
                $this->getParameter('kernel.project_dir').'/public/files_produits',
                $newFilename
            );
        } catch (FileException) {
            $this->addFlash('error', 'Erreur lors du téléversement de l\'image.');
            return $this->redirectToRoute('produit_add');
        }
        $imagePath = 'files_produits/'.$newFilename;

        $connection->insert('produit', [
            'vendeur_username' => $user->getUsername(),
            'nom_produit' => $nomProduit,
            'prix' => $prix,
            'quantite' => $quantite,
            'categorie' => $categorie,
            'description' => $description,
            'image_path' => $imagePath,
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $produitId = (int) $connection->lastInsertId();

        $notif->notifyNewProduit($produitId, $user->getUsername(), $nomProduit, $prix);

        $this->addFlash('success', 'Produit ajouté.');
        return $this->redirectToRoute('produit_add');
    }

    #[Route('/produit/{id}', name: 'produit_details', requirements: ['id' => '\d+'])]
    public function details(
        int $id,
        ProduitRepository $produitRepository
    ): Response {
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable');
        }
        $produitsSimilaires = $produitRepository->findBy(['categorie' => $produit->getCategorie()]);
        $produitsSimilaires = array_filter($produitsSimilaires, fn($p) => $p->getId() !== $produit->getId());
        $produitsSimilaires = array_slice($produitsSimilaires, 0, 4);
        return $this->render('produit/index.html.twig', [
            'produit' => $produit,
            'produits_similaires' => $produitsSimilaires,
        ]);
    }
}
