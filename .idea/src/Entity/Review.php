<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'review', cascade: ['persist', 'remove'])]
    private ?DealRequest $id_deal = null;



    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Client $client_username = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Vendeur $vendeur_username = null;

    #[ORM\Column]
    private int $rating;

    #[ORM\Column(type: Types::TEXT)]
    private string $commentaire ;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

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

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

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
