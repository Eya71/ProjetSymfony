<?php

namespace App\Controller;

use App\Security\LegacyUser;
use Doctrine\DBAL\Connection;
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
            return $this->redirectToRoute('app_client_interface');
        }

        return $this->render('auth/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'last_role' => $request->getSession()->get('last_login_role', 'client'),
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, Connection $connection, UserPasswordHasherInterface $passwordHasher): Response
    {
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
                    throw new \RuntimeException('Mot de passe trop court (minimum 6 caracteres).');
                }

                if (!in_array($old['role'], ['client', 'vendeur'], true)) {
                    throw new \RuntimeException('Role invalide.');
                }

                if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Email invalide.');
                }

                $image = $request->files->get('image');
                if (!$image instanceof UploadedFile) {
                    throw new \RuntimeException('Veuillez ajouter une photo.');
                }

                $exists = (int) $connection->fetchOne('SELECT COUNT(*) FROM client WHERE username = :username', ['username' => $old['username']])
                    + (int) $connection->fetchOne('SELECT COUNT(*) FROM vendeur WHERE username = :username', ['username' => $old['username']]);
                if ($exists > 0) {
                    throw new \RuntimeException("Ce nom d'utilisateur existe deja.");
                }

                $safeName = uniqid('', true).'_'.preg_replace('/[^A-Za-z0-9_.-]/', '_', $image->getClientOriginalName());
                $image->move($this->getParameter('kernel.project_dir').'/public/files_profil', $safeName);
                $storedPhotoPath = '../files_profil/'.$safeName;

                $user = new LegacyUser($old['username'], $old['role']);
                $table = $old['role'] === 'vendeur' ? 'vendeur' : 'client';
                $connection->insert($table, [
                    'username' => $old['username'],
                    'email' => $old['email'],
                    'adresse' => $old['adresse'],
                    'num_tel' => $old['tel'],
                    'id_photo' => $storedPhotoPath,
                    'pwd' => $passwordHasher->hashPassword($user, $password),
                ]);

                return $this->redirectToRoute($old['role'] === 'client' ? 'app_login' : 'app_home');
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
