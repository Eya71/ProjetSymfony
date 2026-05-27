<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Vendeur;
use App\Repository\ClientRepository;
use App\Repository\VendeurRepository;
use App\Security\LegacyUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser() instanceof LegacyUser) {

            if ($this->isGranted('ROLE_VENDEUR')) {
                return $this->redirectToRoute('app_vendeur_commandes');
            }

            if ($this->isGranted('ROLE_CLIENT')) {
                return $this->redirectToRoute('app_client_interface');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'last_role' => $request->getSession()->get('last_login_role', 'client'),
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        ClientRepository $clientRepository,
        VendeurRepository $vendeurRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $error = '';

        $old = [
            'username' => '',
            'tel' => '',
            'email' => '',
            'adresse' => '',
            'role' => 'client',
        ];

        if ($request->isMethod('POST')) {
            $old = [
                'username' => trim((string) $request->request->get('username', '')),
                'tel' => trim((string) $request->request->get('tel', '')),
                'email' => trim((string) $request->request->get('email', '')),
                'adresse' => trim((string) $request->request->get('adresse', '')),
                'role' => (string) $request->request->get('role', 'client'),
            ];

            try {
                $password = (string) $request->request->get('password', '');
                $confirmPassword = (string) $request->request->get('confirmPassword', '');

                if ($old['username'] === '' || $password === '' || $confirmPassword === '') {
                    throw new \RuntimeException('Veuillez remplir tous les champs obligatoires.');
                }

                if ($password !== $confirmPassword) {
                    throw new \RuntimeException('Les mots de passe ne correspondent pas.');
                }

                if (strlen($password) < 6) {
                    throw new \RuntimeException('Mot de passe trop court. Minimum 6 caractères.');
                }

                if (!in_array($old['role'], ['client', 'vendeur'], true)) {
                    throw new \RuntimeException('Rôle invalide.');
                }

                if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Email invalide.');
                }

                $image = $request->files->get('image');

                if (!$image instanceof UploadedFile) {
                    throw new \RuntimeException('Veuillez ajouter une photo.');
                }

                /*
                 * Ici on vérifie si le username existe déjà
                 * avec les Repository, pas avec SELECT.
                 */
                $clientExists = $clientRepository->findOneBy([
                    'username' => $old['username'],
                ]);

                $vendeurExists = $vendeurRepository->findOneBy([
                    'username' => $old['username'],
                ]);

                if ($clientExists !== null || $vendeurExists !== null) {
                    throw new \RuntimeException("Ce nom d'utilisateur existe déjà.");
                }

                /*
                 * Préparation et upload de la photo.
                 */
                $safeName = uniqid('', true) . '_' . preg_replace(
                        '/[^A-Za-z0-9_.-]/',
                        '_',
                        $image->getClientOriginalName()
                    );

                $image->move(
                    $this->getParameter('kernel.project_dir') . '/public/files_profil',
                    $safeName
                );

                $storedPhotoPath = '../files_profil/' . $safeName;

                /*
                 * LegacyUser sert à hasher le mot de passe correctement.
                 */
                $legacyUser = new LegacyUser($old['username'], $old['role']);

                $hashedPassword = $passwordHasher->hashPassword($legacyUser, $password);

                /*
                 * Si l'utilisateur choisit Client,
                 * on crée une entité Client.
                 */
                if ($old['role'] === 'client') {
                    $client = new Client();

                    $client->setUsername($old['username']);
                    $client->setEmail($old['email']);
                    $client->setAdresse($old['adresse']);
                    $client->setNumTel($old['tel']);
                    $client->setIdPhoto($storedPhotoPath);
                    $client->setPwd($hashedPassword);

                    $entityManager->persist($client);
                }

                /*
                 * Si l'utilisateur choisit Vendeur,
                 * on crée une entité Vendeur.
                 */
                if ($old['role'] === 'vendeur') {
                    $vendeur = new Vendeur();

                    $vendeur->setUsername($old['username']);
                    $vendeur->setEmail($old['email']);
                    $vendeur->setAdresse($old['adresse']);
                    $vendeur->setNumTel($old['tel']);
                    $vendeur->setIdPhoto($storedPhotoPath);
                    $vendeur->setPwd($hashedPassword);
                    $vendeur->setCreatedAt(new \DateTimeImmutable());

                    $entityManager->persist($vendeur);
                }

                /*
                 * Ici Symfony enregistre dans la base.
                 * Il va faire INSERT automatiquement.
                 */
                $entityManager->flush();

                /*
                 * On garde le rôle choisi pour que le radio button soit coché dans login.
                 */
                $request->getSession()->set('last_login_role', $old['role']);

                return $this->redirectToRoute('app_login');

            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('auth/signup.html.twig', [
            'error' => $error,
            'old' => $old,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Logout is handled by Symfony Security.');
    }
}