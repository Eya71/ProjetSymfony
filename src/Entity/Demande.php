<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $nom_produit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $lien_produit = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $id_photo = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    private ?Client $username = null;

    #[ORM\Column(length: 20)]
    private ?string $etat = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'id_demande')]
    private Collection $commandes;

    /**
     * @var Collection<int, DealRequest>
     */
    #[ORM\OneToMany(targetEntity: DealRequest::class, mappedBy: 'id_demande')]
    private Collection $dealRequests;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->dealRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nom_produit;
    }

    public function setNomProduit(string $nom_produit): static
    {
        $this->nom_produit = $nom_produit;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getLienProduit(): ?string
    {
        return $this->lien_produit;
    }

    public function setLienProduit(string $lien_produit): static
    {
        $this->lien_produit = $lien_produit;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $descrption): static
    {
        $this->descrption = $description;

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

    public function getIdPhoto(): ?string
    {
        return $this->id_photo;
    }

    public function setIdPhoto(string $id_photo): static
    {
        $this->id_photo = $id_photo;

        return $this;
    }

    public function getUsername(): ?Client
    {
        return $this->username;
    }

    public function setUsername(?Client $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setIdDemande($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getIdDemande() === $this) {
                $commande->setIdDemande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DealRequest>
     */
    public function getDealRequests(): Collection
    {
        return $this->dealRequests;
    }

    public function addDealRequest(DealRequest $dealRequest): static
    {
        if (!$this->dealRequests->contains($dealRequest)) {
            $this->dealRequests->add($dealRequest);
            $dealRequest->setIdDemande($this);
        }

        return $this;
    }

    public function removeDealRequest(DealRequest $dealRequest): static
    {
        if ($this->dealRequests->removeElement($dealRequest)) {
            // set the owning side to null (unless already changed)
            if ($dealRequest->getIdDemande() === $this) {
                $dealRequest->setIdDemande(null);
            }
        }

        return $this;
    }
}
