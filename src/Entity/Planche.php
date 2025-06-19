<?php

namespace App\Entity;

use App\Enum\PlancheUsage;
use App\Repository\PlancheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PlancheRepository::class)]
#[ORM\Table(
    name: 'planche',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_planche_nom_type', columns: ['nom', 'type'])],
    indexes: [
        new ORM\Index(name: 'idx_planche_nom', columns: ['nom']),
        new ORM\Index(name: 'idx_planche_type', columns: ['type'])
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Planche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column(type: 'string', enumType: PlancheUsage::class)]
    private PlancheUsage $type;

    /**
     * SEULE  = planche à l’unité  
     * INCLUSE = vendue dans un pack
     */
    #[ORM\Column(length: 7)]
    #[Assert\Choice(choices: ['SEULE', 'INCLUSE'])]
    private string $usage = 'SEULE';

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank]
    #[Assert\Json]
    private array $formats = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $prixEcole;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    private string $prixParents;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Assert\Callback]
    public function validatePrix(ExecutionContextInterface $context): void
    {
        if (bccomp($this->prixEcole, $this->prixParents, 2) === 1) {
            $context->buildViolation('Le prix école doit être inférieur ou égal au prix parents.')
                ->atPath('prixEcole')
                ->addViolation();
        }
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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