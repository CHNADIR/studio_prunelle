<?php

namespace App\Entity;

use App\Repository\PriseDeVueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[ORM\Entity(repositoryClass: PriseDeVueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PriseDeVue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: "Le nombre d'élèves est obligatoire")]
    #[Assert\GreaterThan(0, message: "Le nombre d'élèves doit être supérieur à 0")]
    private ?int $nbEleves = null;
    
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le nombre de classes ne peut pas être négatif")]
    private ?int $nbClasses = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $classes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix école ne peut pas être négatif")]
    private ?string $prixEcole = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix parents ne peut pas être négatif")]
    private ?string $prixParents = null;

    #[ORM\ManyToOne(inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'école est obligatoire")]
    private ?Ecole $ecole = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le photographe est obligatoire")]
    private ?User $photographe = null;

    #[ORM\ManyToOne(inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypePrise $typePrise = null;

    #[ORM\ManyToOne(inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(nullable: true)]
    private ?TypeVente $typeVente = null;

    #[ORM\ManyToOne(inversedBy: 'prisesDeVue')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Theme $theme = null;
    
    #[ORM\ManyToMany(targetEntity: Planche::class, inversedBy: 'prisesDeVue')]
    #[ORM\JoinTable(name: 'prise_de_vue_planches_individuelles')]
    private Collection $planchesIndividuelles;
    
    #[ORM\ManyToMany(targetEntity: Planche::class)]
    #[ORM\JoinTable(name: 'prise_de_vue_planches_fratries')]
    private Collection $planchesFratries;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct()
    {
        // Initialisation des collections, dates, etc.
        $this->planchesIndividuelles = new ArrayCollection();
        $this->planchesFratries = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
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

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): self
    {
        $this->classes = $classes;
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
    
    public function getPrixEcole(): ?string
    {
        return $this->prixEcole;
    }

    public function setPrixEcole(?string $prixEcole): self
    {
        $this->prixEcole = $prixEcole;
        return $this;
    }
    
    public function getPrixParents(): ?string
    {
        return $this->prixParents;
    }

    public function setPrixParents(?string $prixParents): self
    {
        $this->prixParents = $prixParents;
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

    public function getPhotographe(): ?User
    {
        return $this->photographe;
    }

    public function setPhotographe(?User $photographe): self
    {
        $this->photographe = $photographe;
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
    
    /**
     * @return Collection<int, Planche>
     */
    public function getPlanchesIndividuelles(): Collection
    {
        return $this->planchesIndividuelles;
    }

    public function addPlancheIndividuelle(Planche $planche): self
    {
        if (!$this->planchesIndividuelles->contains($planche)) {
            $this->planchesIndividuelles->add($planche);
        }

        return $this;
    }

    public function removePlancheIndividuelle(Planche $planche): self
    {
        $this->planchesIndividuelles->removeElement($planche);
        return $this;
    }
    
    /**
     * @return Collection<int, Planche>
     */
    public function getPlanchesFratries(): Collection
    {
        return $this->planchesFratries;
    }

    public function addPlancheFratrie(Planche $planche): self
    {
        if (!$this->planchesFratries->contains($planche)) {
            $this->planchesFratries->add($planche);
        }

        return $this;
    }

    public function removePlancheFratrie(Planche $planche): self
    {
        $this->planchesFratries->removeElement($planche);
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
}