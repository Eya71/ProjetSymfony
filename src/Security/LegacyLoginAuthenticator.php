<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LegacyLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LegacyUserProvider $userProvider,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = trim((string) $request->request->get('username', ''));
        $role = (string) $request->request->get('role', 'client');
        $password = (string) $request->request->get('password', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);
        $request->getSession()->set('last_login_role', $role);

        return new Passport(
            new UserBadge(
                $role . '|' . $username,
                fn (string $identifier): LegacyUser => $this->userProvider->loadUserByIdentifier($identifier)
            ),
            new CustomCredentials(function (string $plainPassword, LegacyUser $user): bool {
                $storedPassword = (string) $user->getPassword();

                if ($storedPassword === '') {
                    return false;
                }

                $passwordInfo = password_get_info($storedPassword);

                if (($passwordInfo['algo'] ?? null) !== 0 && ($passwordInfo['algo'] ?? null) !== null) {
                    return $this->passwordHasher->isPasswordValid($user, $plainPassword);
                }

                return hash_equals($storedPassword, $plainPassword);
            }, $password),
            [
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        /*
         * On récupère l'utilisateur connecté après le login.
         */
        $user = $token->getUser();

        /*
         * Dans ton système, l'utilisateur connecté est un LegacyUser.
         * Donc on vérifie son rôle avec getLegacyRole().
         */
        if ($user instanceof LegacyUser) {

            /*
             * Si c'est un client, on l'envoie vers l'interface client.
             */
            if ($user->getLegacyRole() === 'client') {
                return new RedirectResponse(
                    $this->urlGenerator->generate('app_client_interface')
                );
            }

            /*
             * Si c'est un vendeur, on l'envoie vers le dashboard vendeur.
             */
            if ($user->getLegacyRole() === 'vendeur') {
                return new RedirectResponse(
                    $this->urlGenerator->generate('vendeur_dashboard')
                );
            }
        }

        /*
         * Si le rôle n'est pas reconnu, on revient vers login.
         */
        return new RedirectResponse(
            $this->urlGenerator->generate('app_login')
        );
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->getLoginUrl($request));
    }

    /**
     * Cette méthode est obligatoire quand on utilise AbstractLoginFormAuthenticator.
     *
     * Elle indique à Symfony quelle route utiliser pour afficher la page de login.
     *
     * Si un utilisateur non connecté essaie d'accéder à une page protégée,
     * Symfony va le rediriger vers cette URL.
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }
}