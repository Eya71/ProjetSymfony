<?php

namespace App\Controller;

use App\Repository\VendeurRepository;
use App\Security\LegacyUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VendeurProfileController extends AbstractController
{
    #[Route('/profil/vendeur', name: 'app_vendeur_profile')]
    public function index(
        Request $request,
        VendeurRepository $vendeurRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        $user = $this->getUser();
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';

        if ($username === '') {
            return $this->redirectToRoute('app_home');
        }

        $vendeur = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$vendeur) {
            throw $this->createNotFoundException('Vendeur introuvable');
        }

        if ($request->isMethod('POST')) {
            $vendeur->setEmail($request->request->get('email'));
            $vendeur->setAdresse($request->request->get('adresse'));
            $vendeur->setNumTel($request->request->get('num_tel'));

            $imageFile = $request->files->get('image');

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/files_profil',
                        $newFilename
                    );

                    $vendeur->setIdPhoto('files_profil/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec succès.');

            return $this->redirectToRoute('app_vendeur_profile');
        }

        return $this->render('user_profile/index.html.twig', [
            'vendeur' => $vendeur,
        ]);
    }

#[Route('/vendeur/{username}', name: 'vendor_profile')]
public function publicProfile(
    string $username,
    VendeurRepository $vendeurRepository
): Response {

    $vendeur = $vendeurRepository->findOneBy([
        'username' => $username
    ]);

    if (!$vendeur) {

        throw $this->createNotFoundException(
            'Vendeur introuvable'
        );

    }

    return $this->render(
        'vendor_profile_client/index.html.twig',
        [
            'vendeur' => $vendeur
        ]
    );

}

}