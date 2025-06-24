<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[UniqueEntity(fields: ['libelle'], message: 'Ce thème existe déjà')]
#[ORM\HasLifecycleCallbacks]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Libellé du thème selon cahier des charges
     * Exemples : nature, école, musique
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $libelle = null;

    /**
     * Description détaillée du thème
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Assert\Type(type: 'bool')]
    private bool $active = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: PriseDeVue::class)]
    private Collection $prisesDeVue;

    public function __construct()
    {
        $this->prisesDeVue = new ArrayCollection();
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

    /**
     * @return Collection<int, PriseDeVue>
     */
    public function getPrisesDeVue(): Collection
    {
        return $this->prisesDeVue;
    }

    public function addPriseDeVue(PriseDeVue $priseDeVue): self
    {
        if (!$this->prisesDeVue->contains($priseDeVue)) {
            $this->prisesDeVue->add($priseDeVue);
            $priseDeVue->setTheme($this);
        }

        return $this;
    }

    public function removePriseDeVue(PriseDeVue $priseDeVue): self
    {
        if ($this->prisesDeVue->removeElement($priseDeVue)) {
            // set the owning side to null (unless already changed)
            if ($priseDeVue->getTheme() === $this) {
                $priseDeVue->setTheme(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? 'Thème';
    }
}