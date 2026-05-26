<?php

namespace App\Security;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class LegacyUserProvider implements UserProviderInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        [$role, $username] = str_contains($identifier, '|')
            ? explode('|', $identifier, 2)
            : ['client', $identifier];

        if (!in_array($role, ['client', 'vendeur'], true)) {
            throw new UserNotFoundException();
        }

        $table = $role === 'vendeur' ? 'vendeur' : 'client';
        $user = $this->connection->fetchAssociative(
            "SELECT username, email, adresse, num_tel, idphoto, password FROM {$table} WHERE username = :username",
            ['username' => $username]
        );

        if (!$user) {
            throw new UserNotFoundException();
        }

        return new LegacyUser($user['username'], $role, $user['password'] ?? null, $user);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof LegacyUser) {
            throw new UserNotFoundException();
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return LegacyUser::class === $class || is_subclass_of($class, LegacyUser::class);
    }
}
