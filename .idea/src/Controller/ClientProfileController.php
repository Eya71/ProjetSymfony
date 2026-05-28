<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Security\LegacyUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientProfileController extends AbstractController
{
    #[Route('/profil/client', name: 'app_client_profile')]
    public function index(
        Request $request,
        ClientRepository $clientRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'client') {
            return $this->redirectToRoute('app_home');
        }

        $username = $user->getUsername();

        $client = $clientRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable');
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

            return $this->redirectToRoute('app_client_profile');
        }

        return $this->render('user_profile/index.html.twig', [
            'client' => $client,
        ]);
    }


}
