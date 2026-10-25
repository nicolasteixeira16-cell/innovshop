<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Index(name: 'idx_produit_date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'idx_produit_statut', columns: ['statut'])]
class Produit
{
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_PUBLIE = 'publie';
    public const STATUT_REFUSE = 'refuse';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente de validation',
        self::STATUT_PUBLIE => 'Publié',
        self::STATUT_REFUSE => 'Refusé',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 150)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\Positive(message: 'Le prix doit être supérieur à 0.')]
    private ?string $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $enVedette = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $vendeur = null;

    #[ORM\Column(length: 20, options: ['default' => self::STATUT_PUBLIE])]
    private string $statut = self::STATUT_PUBLIE;

    /**
     * @var Collection<int, OptionProduit>
     */
    #[ORM\OneToMany(targetEntity: OptionProduit::class, mappedBy: 'produit', orphanRemoval: true)]
    private Collection $optionProduits;

    public function __construct()
    {
        $this->optionProduits = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function isEnVedette(): ?bool
    {
        return $this->enVedette;
    }

    public function setEnVedette(bool $enVedette): static
    {
        $this->enVedette = $enVedette;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getVendeur(): ?Utilisateur
    {
        return $this->vendeur;
    }

    public function setVendeur(?Utilisateur $vendeur): static
    {
        $this->vendeur = $vendeur;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getStatutLibelle(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function isPublie(): bool
    {
        return self::STATUT_PUBLIE === $this->statut;
    }

    /**
     * @return Collection<int, OptionProduit>
     */
    public function getOptionProduits(): Collection
    {
        return $this->optionProduits;
    }

    public function addOptionProduit(OptionProduit $optionProduit): static
    {
        if (!$this->optionProduits->contains($optionProduit)) {
            $this->optionProduits->add($optionProduit);
            $optionProduit->setProduit($this);
        }

        return $this;
    }

    public function removeOptionProduit(OptionProduit $optionProduit): static
    {
        if ($this->optionProduits->removeElement($optionProduit)) {
            // set the owning side to null (unless already changed)
            if ($optionProduit->getProduit() === $this) {
                $optionProduit->setProduit(null);
            }
        }

        return $this;
    }
}
