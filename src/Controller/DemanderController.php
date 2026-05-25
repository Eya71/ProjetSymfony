<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


final class DemanderController extends AbstractController
{
    #[Route('/demande', name: 'demande_index')]
    public function index(): Response
    {


        return $this->render(
            'demandes/create.html.twig'
        );

    }
    #[Route('/demande/store', name: 'demande_store', methods: ['POST'])]
    public function store(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $nomProduit =
            $request->request->get('nom_produit');

        $prix =
            $request->request->get('prix');

        $categorie =
            $request->request->get('categorie');

        $description =
            $request->request->get('description');

        $lienProduit =
            $request->request->get('lien_produit');
        $image =
            $request->files->get('image');

        $imagePath = null;
        if (!$image) {

            $this->addFlash(
                'error',
                'Image obligatoire'
            );

            return $this->redirectToRoute(
                'demande_index'
            );

        }
        if ($image) {

            $newFilename =
                uniqid()
                . '.'
                . $image->guessExtension();

            try {

                $image->move(
                    $this->getParameter(
                        'kernel.project_dir'
                    ) . '/public/files_demandes',
                    $newFilename
                );

                $imagePath =
                    'files_demandes/' . $newFilename;

            } catch (FileException $e) {

                $this->addFlash(
                    'error',
                    'Erreur upload image'
                );

                return $this->redirectToRoute(
                    'demande_index'
                );

            }

        }


        $demande = new Demande();

        $demande->setUsername(
            $this->getUser()->getUserIdentifier()
        );

        $demande->setNomProduit(
            $nomProduit
        );

        $demande->setPrix(
            (float)$prix
        );

        $demande->setCategorie(
            $categorie
        );

        $demande->setDescription(
            $description
        );

        $demande->setLienProduit(
            $lienProduit
        );

        $demande->setImagePath(
            $imagePath
        );

        $entityManager->persist($demande);

        $entityManager->flush();


        $this->addFlash(
            'success',
            'Demande publiée'
        );

        return $this->redirectToRoute(
            'demande_index'
        );

    }

}
