<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class LegacyUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly string $username,
        private readonly string $role,
        private readonly ?string $password = null,
        private readonly array $data = [],
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->role.'|'.$this->username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getLegacyRole(): string
    {
        return $this->role;
    }

    public function getRoles(): array
    {
        return [$this->role === 'vendeur' ? 'ROLE_VENDEUR' : 'ROLE_CLIENT'];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function eraseCredentials(): void
    {
    }
}
