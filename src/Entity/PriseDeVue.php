<?php

namespace App\Entity;

use App\Repository\PriseDeVueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriseDeVueRepository::class)]
class PriseDeVue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date n'est pas valide.")]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du photographe est obligatoire.")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom du photographe doit contenir au moins {{ limit }} caractères.", maxMessage: "Le nom du photographe ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $photographe = null;

    #[ORM\ManyToOne(inversedBy: 'priseDeVues')]
    #[Assert\NotNull(message: "L'école est obligatoire.")]
    #[Assert\Valid]
    private ?Ecole $ecole = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: "Le type de prise est obligatoire.")]
    #[Assert\Valid]
    private ?TypePrise $typePrise = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull(message: "Le thème est obligatoire.")]
    #[Assert\Valid]
    private ?Theme $theme = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre d'élèves est obligatoire.")]
    #[Assert\Positive(message: "Le nombre d'élèves doit être un nombre positif.")]
    private ?int $nombreEleves = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre de classes est obligatoire.")]
    #[Assert\Positive(message: "Le nombre de classes doit être un nombre positif.")]
    private ?int $nombreClasses = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le type de vente est obligatoire.")]
    #[Assert\Valid]
    private ?TypeVente $typeVente = null;

    /**
     * @var Collection<int, Planche>
     */
    #[ORM\ManyToMany(targetEntity: Planche::class)]
    #[ORM\JoinTable(name: 'prise_individuel_planches')]
    #[Assert\Valid]
    private Collection $planchesIndividuel;

    /**
     * @var Collection<int, Planche>
     */
    #[ORM\ManyToMany(targetEntity: Planche::class)]
    #[ORM\JoinTable(name: 'prise_fratrie_planches')]
    #[Assert\Valid]
    private Collection $planchesFratrie;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix école est obligatoire.")]
    #[Assert\Positive(message: "Le prix école doit être un nombre positif.")]
    #[Assert\Regex(pattern: "/^\d+(\.\d{1,2})?$/", message: "Le prix école doit être un nombre décimal valide (ex: 10.50).")]
    private ?string $prixEcole = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix parent est obligatoire.")]
    #[Assert\Positive(message: "Le prix parent doit être un nombre positif.")]
    #[Assert\Regex(pattern: "/^\d+(\.\d{1,2})?$/", message: "Le prix parent doit être un nombre décimal valide (ex: 10.50).")]
    private ?string $prixParent = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 5000, maxMessage: "Le commentaire ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->planchesIndividuel = new ArrayCollection();
        $this->planchesFratrie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPhotographe(): ?string
    {
        return $this->photographe;
    }

    public function setPhotographe(string $photographe): static
    {
        $this->photographe = $photographe;

        return $this;
    }

    public function getEcole(): ?Ecole
    {
        return $this->ecole;
    }

    public function setEcole(?Ecole $ecole): static
    {
        $this->ecole = $ecole;

        return $this;
    }

    public function getTypePrise(): ?TypePrise
    {
        return $this->typePrise;
    }

    public function setTypePrise(?TypePrise $typePrise): static
    {
        $this->typePrise = $typePrise;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getNombreEleves(): ?int
    {
        return $this->nombreEleves;
    }

    public function setNombreEleves(int $nombreEleves): static
    {
        $this->nombreEleves = $nombreEleves;

        return $this;
    }

    public function getNombreClasses(): ?int
    {
        return $this->nombreClasses;
    }

    public function setNombreClasses(int $nombreClasses): static
    {
        $this->nombreClasses = $nombreClasses;

        return $this;
    }

    public function getTypeVente(): ?TypeVente
    {
        return $this->typeVente;
    }

    public function setTypeVente(?TypeVente $typeVente): static
    {
        $this->typeVente = $typeVente;

        return $this;
    }

    /**
     * @return Collection<int, Planche>
     */
    public function getPlanchesIndividuel(): Collection
    {
        return $this->planchesIndividuel;
    }

    public function addPlanchesIndividuel(Planche $planchesIndividuel): static
    {
        if (!$this->planchesIndividuel->contains($planchesIndividuel)) {
            $this->planchesIndividuel->add($planchesIndividuel);
        }

        return $this;
    }

    public function removePlanchesIndividuel(Planche $planchesIndividuel): static
    {
        $this->planchesIndividuel->removeElement($planchesIndividuel);

        return $this;
    }

    /**
     * @return Collection<int, Planche>
     */
    public function getPlanchesFratrie(): Collection
    {
        return $this->planchesFratrie;
    }

    public function addPlanchesFratrie(Planche $planchesFratrie): static
    {
        if (!$this->planchesFratrie->contains($planchesFratrie)) {
            $this->planchesFratrie->add($planchesFratrie);
        }

        return $this;
    }

    public function removePlanchesFratrie(Planche $planchesFratrie): static
    {
        $this->planchesFratrie->removeElement($planchesFratrie);

        return $this;
    }

    public function getPrixEcole(): ?string
    {
        return $this->prixEcole;
    }

    public function setPrixEcole(string $prixEcole): static
    {
        $this->prixEcole = $prixEcole;

        return $this;
    }

    public function getPrixParent(): ?string
    {
        return $this->prixParent;
    }

    public function setPrixParent(string $prixParent): static
    {
        $this->prixParent = $prixParent;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function __toString(): string
    {
        return $this->photographe; // ou une autre propriété appropriée
    }
}
