<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_notif')]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $recipient_username = null;

    #[ORM\Column(length: 10)]
    private ?string $recipient_role = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(length: 120)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $actor_username = null;

    #[ORM\Column(nullable: true)]
    private ?int $related_id = null;

    #[ORM\Column]
    private ?bool $is_read = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipientUsername(): ?string
    {
        return $this->recipient_username;
    }

    public function setRecipientUsername(string $recipient_username): static
    {
        $this->recipient_username = $recipient_username;

        return $this;
    }

    public function getRecipientRole(): ?string
    {
        return $this->recipient_role;
    }

    public function setRecipientRole(string $recipient_role): static
    {
        $this->recipient_role = $recipient_role;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getActorUsername(): ?string
    {
        return $this->actor_username;
    }

    public function setActorUsername(?string $actor_username): static
    {
        $this->actor_username = $actor_username;

        return $this;
    }

    public function getRelatedId(): ?int
    {
        return $this->related_id;
    }

    public function setRelatedId(?int $related_id): static
    {
        $this->related_id = $related_id;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
