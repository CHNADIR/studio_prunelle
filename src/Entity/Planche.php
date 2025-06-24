<?php

namespace App\Entity;

use App\Repository\PlancheRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlancheRepository::class)]
#[UniqueEntity(fields: ['libelle'], message: 'Cette planche existe déjà')]
#[ORM\HasLifecycleCallbacks]
class Planche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Libellé de la planche selon cahier des charges
     * Exemples : pochette en plastique, cartonnage, album, albums nvx arrivants
     */
    #[ORM\Column(length: 200, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire')]
    #[Assert\Length(
        max: 200,
        maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $libelle = null;

    /**
     * Description détaillée de la planche
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Planche active ou inactive
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->active = true;
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->libelle ?? 'Planche';
    }
}