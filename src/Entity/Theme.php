<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// Si vous voulez UniqueEntity, ajoutez :
// use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
// #[UniqueEntity(fields: ['nom'], message: 'Ce thème existe déjà.')] // Si le nom doit être unique
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true, unique: true)] // unique: true pour la BDD
    #[Assert\NotBlank(message: "Le nom du thème ne peut pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le nom du thème ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    // La propriété $email semble déplacée ici. Si elle est nécessaire, ajoutez des Assertions.
    // Sinon, envisagez de la supprimer.
    // #[Assert\NotBlank]
    // #[Assert\Email]
    // private ?string $email = null; 

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

    public function __toString(): string
    {
        return $this->nom;
    }
}
