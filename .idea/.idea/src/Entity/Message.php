<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?DealRequest $id_deal = null;

    #[ORM\Column(length: 30)]
    private ?string $sender_username = null;

    #[ORM\Column(length: 30)]
    private ?string $receiver_username = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?bool $is_read = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDeal(): ?DealRequest
    {
        return $this->id_deal;
    }

    public function setIdDeal(?DealRequest $id_deal): static
    {
        $this->id_deal = $id_deal;

        return $this;
    }

    public function getSenderUsername(): ?string
    {
        return $this->sender_username;
    }

    public function setSenderUsername(string $sender_username): static
    {
        $this->sender_username = $sender_username;

        return $this;
    }

    public function getReceiverUsername(): ?string
    {
        return $this->receiver_username;
    }

    public function setReceiverUsername(string $receiver_username): static
    {
        $this->receiver_username = $receiver_username;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->is_read;
    }

    public function setIsRead(bool $is_read): static
    {
        $this->is_read = $is_read;

        return $this;
    }
}
