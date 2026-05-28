<?php

namespace App\Controller;

use App\Security\LegacyUser;
use App\Service\NotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemanderController extends AbstractController
{
    #[Route('/demande', name: 'demande_index')]
    public function index(): Response
    {
        return $this->render('demandes/create.html.twig');
    }

    #[Route('/demande/store', name: 'demande_store', methods: ['POST'])]
    public function store(
        Request $request,
        Connection $connection,
        NotificationService $notif
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'client') {
            return $this->redirectToRoute('app_login');
        }

        $nomProduit = trim((string) $request->request->get('nom_produit', ''));
        $prix = (string) $request->request->get('prix', '');
        $categorie = trim((string) $request->request->get('categorie', ''));
        $description = trim((string) $request->request->get('description', ''));
        $lienProduit = trim((string) $request->request->get('lien_produit', ''));
        $image = $request->files->get('image');

        if ($nomProduit === '' || $prix === '' || $categorie === '' || !$image) {
            $this->addFlash('error', 'Veuillez remplir tous les champs et joindre une image.');
            return $this->redirectToRoute('demande_index');
        }

        $newFilename = uniqid().'.'.$image->guessExtension();
        try {
            $image->move(
                $this->getParameter('kernel.project_dir').'/public/files_demandes',
                $newFilename
            );
        } catch (FileException) {
            $this->addFlash('error', 'Erreur lors du téléversement de l\'image.');
            return $this->redirectToRoute('demande_index');
        }
        $imagePath = 'files_demandes/'.$newFilename;

        $connection->insert('demande', [
            'nom_produit' => $nomProduit,
            'prix' => $prix,
            'lien_produit' => $lienProduit,
            'description' => $description,
            'categorie' => $categorie,
            'id_photo' => $imagePath,

            'username_id' => $connection->fetchOne(
            'SELECT id FROM client WHERE username = :username',
                ['username' => $user->getUsername()]),
            'etat' => 'en attente',
        ]);
        $demandeId = (int) $connection->lastInsertId();

        $notif->notifyNewDemande($demandeId, $user->getUsername(), $nomProduit, $prix);

        $this->addFlash('success', 'Demande publiée avec succès.');
        return $this->redirectToRoute('demande_index');
    }
}
