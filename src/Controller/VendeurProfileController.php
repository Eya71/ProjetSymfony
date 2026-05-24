<?php

namespace App\Controller;

use App\Repository\VendeurRepository;
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
        $session = $request->getSession();

        $userSession = $session->get('user');

        if (!$userSession || empty($userSession['username'])) {
            return $this->redirectToRoute('app_login');
        }

        $username = $userSession['username'];

        $client = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$client) {
            throw $this->createNotFoundException('vendeur introuvable');
        }

        if ($request->isMethod('POST')) {
            $client->setEmail($request->request->get('email'));
            $client->setAdresse($request->request->get('adresse'));
            $client->setNumTel($request->request->get('num_tel'));

            $imageFile = $request->files->get('image');

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/files_profil',
                        $newFilename       #kernel.project_dir donne le chemin racine de  projet Symfony: C:\Users\DELL\PhpstormProjects\ProjetSymfony#
                    );

                    $client->setIdPhoto('files_profil/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec succès.');

            return $this->redirectToRoute('app_vendeur_profile');
        }

        return $this->render('user_profile/index.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/test-login-vendeur', name: 'app_test_login_vendeur')]
    public function testLogin(Request $request): Response
    {
        $session = $request->getSession();

        $session->set('user', [
            'username' => 'mohamedabbes',
            'role' => 'vendeur',
        ]);

        return $this->redirectToRoute('app_vendeur_profile');
    }
}