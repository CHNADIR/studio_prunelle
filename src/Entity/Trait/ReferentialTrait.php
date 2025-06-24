<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait pour les propriétés communes des entités référentielles
 * Pattern appliqué: Trait Pattern (patterns.md)
 * 
 * Factorise les propriétés communes :
 * - libelle, description, active, createdAt, updatedAt
 * - validations et contraintes communes
 * - méthodes d'accès standardisées
 */
trait ReferentialTrait
{
    /**
     * Libellé de l'entité référentielle
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $libelle = null;

    /**
     * Description détaillée de l'entité
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Statut actif/inactif de l'entité
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Assert\Type(type: 'bool')]
    private bool $active = true;

    /**
     * Date de création de l'entité
     */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Date de dernière modification
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * Initialise les valeurs par défaut du trait
     */
    protected function initializeReferentialTrait(): void
    {
        $this->active = true;
        $this->createdAt = new \DateTime();
    }

    /**
     * Callback appelé avant la mise à jour
     */
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // === GETTERS ET SETTERS ===

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

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
} 