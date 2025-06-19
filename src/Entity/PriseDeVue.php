<?php

namespace App\Entity;

use App\Repository\PriseDeVueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriseDeVueRepository::class)]
class PriseDeVue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull(message: "La date est obligatoire")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    private ?int $nbEleves = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $classes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

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

    // Constructeur et getters/setters existants

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
}