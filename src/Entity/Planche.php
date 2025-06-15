<?php

namespace App\Entity;

use App\Repository\PlancheRepository;
use Doctrine\DBAL\Types\Types; // Assurez-vous que cette ligne est présente
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlancheRepository::class)]
class Planche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "Le nom de la planche ne peut pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom de la planche ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "La catégorie ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "La catégorie ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionContenu = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageFilename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

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

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): static
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getDescriptionContenu(): ?string
    {
        return $this->descriptionContenu;
    }

    public function setDescriptionContenu(?string $descriptionContenu): static
    {
        $this->descriptionContenu = $descriptionContenu;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom; // ou une autre propriété appropriée
    }
}
