<?php

namespace App\Entity;

use App\Repository\PriseDeVueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriseDeVueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PriseDeVue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Date de la prise de vue (datePdv selon cahier des charges)
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de prise de vue est obligatoire")]
    private ?\DateTimeInterface $datePdv = null;

    /**
     * Nombre d'élèves (nbEleves selon cahier des charges)
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: "Le nombre d'élèves est obligatoire")]
    #[Assert\GreaterThan(0, message: "Le nombre d'élèves doit être supérieur à 0")]
    private ?int $nbEleves = null;
    
    /**
     * Nombre de classes (nbClasses selon cahier des charges)
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le nombre de classes ne peut pas être négatif")]
    private ?int $nbClasses = null;

    /**
     * Prix école (prixÉcole selon cahier des charges)
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix école ne peut pas être négatif")]
    private ?string $prixEcole = null;
    
    /**
     * Prix parent (prixParent selon cahier des charges)
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix parent ne peut pas être négatif")]
    private ?string $prixParent = null;

    /**
     * Commentaires libres
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    // === RELATIONS SELON CAHIER DES CHARGES (SÉLECTION UNIQUE) ===

    /**
     * Relation vers le photographe (User)
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'photographe_id', nullable: false)]
    #[Assert\NotNull(message: "Le photographe est obligatoire")]
    private ?User $photographe = null;

    /**
     * Relation vers l'école
     */
    #[ORM\ManyToOne(targetEntity: Ecole::class, inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(name: 'ecole_id', nullable: false)]
    #[Assert\NotNull(message: "L'école est obligatoire")]
    private ?Ecole $ecole = null;

    /**
     * Relation vers le type de prise (sélection unique)
     */
    #[ORM\ManyToOne(targetEntity: TypePrise::class, inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(name: 'typePrise_id', nullable: true)]
    private ?TypePrise $typePrise = null;

    /**
     * Relation vers le type de vente (sélection unique)
     */
    #[ORM\ManyToOne(targetEntity: TypeVente::class, inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(name: 'typeVente_id', nullable: true)]
    private ?TypeVente $typeVente = null;

    /**
     * Relation vers le thème (sélection unique)
     */
    #[ORM\ManyToOne(targetEntity: Theme::class, inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(name: 'theme_id', nullable: true)]
    private ?Theme $theme = null;

    /**
     * Relation vers les pochettes individuelles (sélection multiple)
     * Pattern: ManyToMany pour permettre la sélection multiple
     */
    #[ORM\ManyToMany(targetEntity: PochetteIndiv::class)]
    #[ORM\JoinTable(name: 'prise_de_vue_pochette_indiv')]
    private Collection $pochettesIndiv;

    /**
     * Relation vers les pochettes fratries (sélection multiple)
     * Pattern: ManyToMany pour permettre la sélection multiple
     */
    #[ORM\ManyToMany(targetEntity: PochetteFratrie::class)]
    #[ORM\JoinTable(name: 'prise_de_vue_pochette_fratrie')]
    private Collection $pochettesFratrie;

    /**
     * Relation vers les planches (sélection multiple)
     * Pattern: ManyToMany pour permettre la sélection multiple
     */
    #[ORM\ManyToMany(targetEntity: Planche::class)]
    #[ORM\JoinTable(name: 'prise_de_vue_planche')]
    private Collection $planches;

    // === TIMESTAMPS ===
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->pochettesIndiv = new ArrayCollection();
        $this->pochettesFratrie = new ArrayCollection();
        $this->planches = new ArrayCollection();
    }
    
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // === GETTERS & SETTERS ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePdv(): ?\DateTimeInterface
    {
        return $this->datePdv;
    }

    public function setDatePdv(?\DateTimeInterface $datePdv): self
    {
        $this->datePdv = $datePdv;
        return $this;
    }

    public function getNbEleves(): ?int
    {
        return $this->nbEleves;
    }

    public function setNbEleves(?int $nbEleves): self
    {
        $this->nbEleves = $nbEleves;
        return $this;
    }
    
    public function getNbClasses(): ?int
    {
        return $this->nbClasses;
    }

    public function setNbClasses(?int $nbClasses): self
    {
        $this->nbClasses = $nbClasses;
        return $this;
    }

    public function getPrixEcole(): ?string
    {
        return $this->prixEcole;
    }

    public function setPrixEcole(?string $prixEcole): self
    {
        $this->prixEcole = $prixEcole;
        return $this;
    }
    
    public function getPrixParent(): ?string
    {
        return $this->prixParent;
    }

    public function setPrixParent(?string $prixParent): self
    {
        $this->prixParent = $prixParent;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getPhotographe(): ?User
    {
        return $this->photographe;
    }

    public function setPhotographe(?User $photographe): self
    {
        $this->photographe = $photographe;
        return $this;
    }

    public function getEcole(): ?Ecole
    {
        return $this->ecole;
    }

    public function setEcole(?Ecole $ecole): self
    {
        $this->ecole = $ecole;
        return $this;
    }

    public function getTypePrise(): ?TypePrise
    {
        return $this->typePrise;
    }

    public function setTypePrise(?TypePrise $typePrise): self
    {
        $this->typePrise = $typePrise;
        return $this;
    }

    public function getTypeVente(): ?TypeVente
    {
        return $this->typeVente;
    }

    public function setTypeVente(?TypeVente $typeVente): self
    {
        $this->typeVente = $typeVente;
        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    // === NOUVELLES MÉTHODES POUR LES COLLECTIONS ===

    /**
     * @return Collection<int, PochetteIndiv>
     */
    public function getPochettesIndiv(): Collection
    {
        return $this->pochettesIndiv;
    }

    public function addPochetteIndiv(PochetteIndiv $pochetteIndiv): self
    {
        if (!$this->pochettesIndiv->contains($pochetteIndiv)) {
            $this->pochettesIndiv->add($pochetteIndiv);
        }

        return $this;
    }

    public function removePochetteIndiv(PochetteIndiv $pochetteIndiv): self
    {
        $this->pochettesIndiv->removeElement($pochetteIndiv);

        return $this;
    }

    /**
     * @return Collection<int, PochetteFratrie>
     */
    public function getPochettesFratrie(): Collection
    {
        return $this->pochettesFratrie;
    }

    public function addPochetteFratrie(PochetteFratrie $pochetteFratrie): self
    {
        if (!$this->pochettesFratrie->contains($pochetteFratrie)) {
            $this->pochettesFratrie->add($pochetteFratrie);
        }

        return $this;
    }

    public function removePochetteFratrie(PochetteFratrie $pochetteFratrie): self
    {
        $this->pochettesFratrie->removeElement($pochetteFratrie);

        return $this;
    }

    /**
     * @return Collection<int, Planche>
     */
    public function getPlanches(): Collection
    {
        return $this->planches;
    }

    public function addPlanche(Planche $planche): self
    {
        if (!$this->planches->contains($planche)) {
            $this->planches->add($planche);
        }

        return $this;
    }

    public function removePlanche(Planche $planche): self
    {
        $this->planches->removeElement($planche);

        return $this;
    }

    // === COMPATIBILITÉ AVEC ANCIENNES MÉTHODES (Deprecated) ===

    /**
     * @deprecated Utiliser getPochettesIndiv()->first() à la place
     */
    public function getPochetteIndiv(): ?PochetteIndiv
    {
        return $this->pochettesIndiv->first() ?: null;
    }

    /**
     * @deprecated Utiliser addPochetteIndiv() à la place
     */
    public function setPochetteIndiv(?PochetteIndiv $pochetteIndiv): self
    {
        $this->pochettesIndiv->clear();
        if ($pochetteIndiv) {
            $this->addPochetteIndiv($pochetteIndiv);
        }
        return $this;
    }

    /**
     * @deprecated Utiliser getPochettesFratrie()->first() à la place
     */
    public function getPochetteFratrie(): ?PochetteFratrie
    {
        return $this->pochettesFratrie->first() ?: null;
    }

    /**
     * @deprecated Utiliser addPochetteFratrie() à la place
     */
    public function setPochetteFratrie(?PochetteFratrie $pochetteFratrie): self
    {
        $this->pochettesFratrie->clear();
        if ($pochetteFratrie) {
            $this->addPochetteFratrie($pochetteFratrie);
        }
        return $this;
    }

    /**
     * @deprecated Utiliser getPlanches()->first() à la place
     */
    public function getPlanche(): ?Planche
    {
        return $this->planches->first() ?: null;
    }

    /**
     * @deprecated Utiliser addPlanche() à la place
     */
    public function setPlanche(?Planche $planche): self
    {
        $this->planches->clear();
        if ($planche) {
            $this->addPlanche($planche);
        }
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

    /**
     * Représentation textuelle de la prise de vue
     */
    public function __toString(): string
    {
        return sprintf(
            'Prise de vue %s - %s (%d élèves)',
            $this->id ? '#' . $this->id : 'nouvelle',
            $this->ecole ? $this->ecole->getNom() : 'École non définie',
            $this->nbEleves ?? 0
        );
    }

    /**
     * Génère un récapitulatif de la prise de vue
     */
    public function getRecapitulatif(): array
    {
        return [
            'date' => $this->datePdv?->format('d/m/Y') ?? 'Non définie',
            'ecole' => $this->ecole?->getNom() ?? 'Non définie',
            'photographe' => $this->photographe?->getNom() ?? 'Non défini',
            'nb_eleves' => $this->nbEleves ?? 0,
            'nb_classes' => $this->nbClasses ?? 0,
            'type_prise' => $this->typePrise?->getLibelle() ?? 'Non défini',
            'theme' => $this->theme?->getLibelle() ?? 'Non défini',
            'nb_pochettes_indiv' => $this->pochettesIndiv->count(),
            'nb_pochettes_fratrie' => $this->pochettesFratrie->count(),
            'nb_planches' => $this->planches->count(),
            'prix_total' => $this->getPrixTotal()
        ];
    }

    /**
     * Calcule le prix total (école + parents)
     */
    public function getPrixTotal(): float
    {
        $total = 0;
        if ($this->prixEcole) $total += (float) $this->prixEcole;
        if ($this->prixParent) $total += (float) $this->prixParent;
        return $total;
    }

    /**
     * Vérifie si la prise de vue est complète
     */
    public function isComplete(): bool
    {
        return $this->datePdv !== null
            && $this->ecole !== null
            && $this->photographe !== null
            && $this->nbEleves !== null
            && $this->nbEleves > 0;
    }

    /**
     * Génère un résumé court de la prise de vue
     */
    public function getResume(): string
    {
        $parts = [];
        
        if ($this->ecole) $parts[] = $this->ecole->getNom();
        if ($this->datePdv) $parts[] = $this->datePdv->format('d/m/Y');
        if ($this->nbEleves) $parts[] = $this->nbEleves . ' élèves';
        
        return implode(' - ', $parts) ?: 'Prise de vue incomplète';
    }
}