<?php

namespace App\Entity;

use App\Repository\DealRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DealRequestRepository::class)]
class DealRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dealRequests')]
    private ?Demande $id_demande = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?Client $client_username = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    private ?Vendeur $vendeur_username = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix_propose = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $client_seen_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $vendeur_seen_at = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'id_deal')]
    private Collection $messages;

    #[ORM\OneToOne(mappedBy: 'id_deal', cascade: ['persist', 'remove'])]
    private ?Review $review = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDemande(): ?Demande
    {
        return $this->id_demande;
    }

    public function setIdDemande(?Demande $id_demande): static
    {
        $this->id_demande = $id_demande;

        return $this;
    }

    public function getClientUsername(): ?Client
    {
        return $this->client_username;
    }

    public function setClientUsername(?Client $client_username): static
    {
        $this->client_username = $client_username;

        return $this;
    }

    public function getVendeurUsername(): ?Vendeur
    {
        return $this->vendeur_username;
    }

    public function setVendeurUsername(?Vendeur $vendeur_username): static
    {
        $this->vendeur_username = $vendeur_username;

        return $this;
    }

    public function getPrixPropose(): ?string
    {
        return $this->prix_propose;
    }

    public function setPrixPropose(string $prix_propose): static
    {
        $this->prix_propose = $prix_propose;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    public function getClientSeenAt(): ?\DateTimeImmutable
    {
        return $this->client_seen_at;
    }

    public function setClientSeenAt(\DateTimeImmutable $client_seen_at): static
    {
        $this->client_seen_at = $client_seen_at;

        return $this;
    }

    public function getVendeurSeenAt(): ?\DateTimeImmutable
    {
        return $this->vendeur_seen_at;
    }

    public function setVendeurSeenAt(\DateTimeImmutable $vendeur_seen_at): static
    {
        $this->vendeur_seen_at = $vendeur_seen_at;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setIdDeal($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getIdDeal() === $this) {
                $message->setIdDeal(null);
            }
        }

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        // unset the owning side of the relation if necessary
        if ($review === null && $this->review !== null) {
            $this->review->setIdDeal(null);
        }

        // set the owning side of the relation if necessary
        if ($review !== null && $review->getIdDeal() !== $this) {
            $review->setIdDeal($this);
        }

        $this->review = $review;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
