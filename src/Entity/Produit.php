<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /*
     * Relation avec le vendeur.
     *
     * Dans la base, la colonne s'appelle vendeur_username_id.
     * Dans Symfony, la propriété s'appelle vendeurUsername.
     */
    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: 'vendeur_username_id', referencedColumnName: 'id')]
    private ?Vendeur $vendeurUsername = null;

    /*
     * Dans la base : nom_produit
     * Dans Symfony : nomProduit
     */
    #[ORM\Column(name: 'nom_produit', length: 255)]
    private ?string $nomProduit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    /*
     * Attention :
     * Ta base semble avoir une colonne "decription" avec faute.
     * Donc on garde name: 'decription' pour correspondre à la base.
     *
     * Si tu renommes la colonne en "description",
     * il faudra changer ici aussi.
     */
    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /*
     * Dans la base : image_path
     * Dans Symfony : imagePath
     */
    #[ORM\Column(name: 'image_path', length: 255)]
    private ?string $imagePath = null;

    /*
     * Dans la base : created_at
     * Dans Symfony : createdAt
     */
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendeurUsername(): ?Vendeur
    {
        return $this->vendeurUsername;
    }

    public function setVendeurUsername(?Vendeur $vendeurUsername): static
    {
        $this->vendeurUsername = $vendeurUsername;

        return $this;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): static
    {
        $this->nomProduit = $nomProduit;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string|float $prix): static
    {
        $this->prix = (string) $prix;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}