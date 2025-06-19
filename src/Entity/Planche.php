<?php

namespace App\Entity;

use App\Repository\PlancheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlancheRepository::class)]
class Planche
{
    public const TYPE_INDIVIDUELLE = 'individuelle';
    public const TYPE_FRATRIE = 'fratrie';
    public const TYPE_SEULE = 'seule';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(max: 100, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    #[Assert\Choice(choices: [self::TYPE_INDIVIDUELLE, self::TYPE_FRATRIE, self::TYPE_SEULE], message: "Type invalide")]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON)]
    private array $formats = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix école est obligatoire")]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix ne peut pas être négatif")]
    private ?string $prixEcole = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix parents est obligatoire")]
    #[Assert\GreaterThanOrEqual(0, message: "Le prix ne peut pas être négatif")]
    private ?string $prixParents = null;
    
    #[ORM\Column]
    private bool $active = true;
    
    #[ORM\ManyToMany(targetEntity: PriseDeVue::class, mappedBy: 'planchesIndividuelles')]
    private Collection $prisesDeVue;

    public function __construct()
    {
        $this->prisesDeVue = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFormats(): array
    {
        return $this->formats;
    }

    public function setFormats(array $formats): self
    {
        $this->formats = $formats;
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
    
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    /**
     * @return Collection<int, PriseDeVue>
     */
    public function getPrisesDeVue(): Collection
    {
        return $this->prisesDeVue;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}