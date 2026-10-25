<?php

namespace App\Entity;

use App\Repository\CandidatureTesteurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureTesteurRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Une candidature existe déjà avec cette adresse e-mail.')]
class CandidatureTesteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.')]
    private ?string $nom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'adresse e-mail est obligatoire.")]
    #[Assert\Email(message: "L'adresse e-mail n'est pas valide.")]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Dites-nous pourquoi vous souhaitez tester nos prototypes.')]
    #[Assert\Length(min: 20, max: 2000, minMessage: 'Merci de détailler un peu (20 caractères minimum).')]
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.')]
    private ?string $motivation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Indiquez les types de produits que vous aimeriez tester.')]
    #[Assert\Length(max: 255, maxMessage: '255 caractères maximum.')]
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Les caractères < et > ne sont pas autorisés.')]
    private ?string $typesProduits = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): static
    {
        $this->motivation = $motivation;

        return $this;
    }

    public function getTypesProduits(): ?string
    {
        return $this->typesProduits;
    }

    public function setTypesProduits(?string $typesProduits): static
    {
        $this->typesProduits = $typesProduits;

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
}
