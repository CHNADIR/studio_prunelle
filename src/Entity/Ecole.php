<?php

namespace App\Entity;

use App\Repository\EcoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\PriseDeVue;

/**
 * Entité représentant une école, avec validations conformes au cahier des charges.
 * 
 * Champs obligatoires selon cahier des charges :
 * - code école: 5 caractères obligatoire (exemple: 25108)
 * - genre, nom, adresse (rue, ville, code postal), contact (téléphone, mail), actif
 */
#[ORM\Entity(repositoryClass: EcoleRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Ce code école existe déjà')]
#[ORM\HasLifecycleCallbacks]
class Ecole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /**
     * Code école : exactement 5 caractères obligatoire selon cahier des charges
     */
    #[ORM\Column(length: 5, unique: true)]
    #[Assert\NotBlank(message: 'Le code école est obligatoire')]
    #[Assert\Length(
        exactly: 5,
        exactMessage: 'Le code école doit faire exactement {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^\d{5}$/',
        message: 'Le code école doit contenir exactement 5 chiffres'
    )]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom de l\'école est obligatoire')]
    #[Assert\Length(
        max: 200,
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nom = null;

    /**
     * Genre de l'école (public, privé, etc.)
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le genre de l\'école est obligatoire')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le genre ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $genre = null;

    /**
     * Adresse rue
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'La ville ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $ville = null;

    /**
     * Code postal français (5 chiffres)
     */
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[Assert\Regex(
        pattern: '/^\d{5}$/',
        message: 'Le code postal doit contenir exactement 5 chiffres'
    )]
    private ?string $codePostal = null;

    /**
     * Numéro de téléphone
     */
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
    #[Assert\Regex(
        pattern: '/^(?:(?:\+|00)33[\s\.\-]{0,3}(?:\(0\)[\s\.\-]{0,3})?|0)[1-9](?:[\s\.\-]?\d{2}){4}$/',
        message: 'Le numéro de téléphone n\'est pas valide'
    )]
    private ?string $telephone = null;

    /**
     * Email de contact (optionnel)
     */
    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'L\'email n\'est pas valide')]
    private ?string $email = null;

    /**
     * École active ? selon cahier des charges
     */
    #[ORM\Column(type: "boolean", options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * Relation avec les prises de vue de cette école
     */
    #[ORM\OneToMany(mappedBy: 'ecole', targetEntity: PriseDeVue::class, cascade: ['persist'])]
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
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

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
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
            $priseDeVue->setEcole($this);
        }

        return $this;
    }

    public function removePriseDeVue(PriseDeVue $priseDeVue): self
    {
        if ($this->prisesDeVue->removeElement($priseDeVue)) {
            if ($priseDeVue->getEcole() === $this) {
                $priseDeVue->setEcole(null);
            }
        }

        return $this;
    }

    /**
     * Retourne l'adresse complète formatée
     */
    public function getAdresseComplete(): string
    {
        return sprintf('%s, %s %s', $this->adresse, $this->codePostal, $this->ville);
    }

    /**
     * Récupère les dernières prises de vue triées par date décroissante
     * 
     * @param int $limit Nombre maximum de prises de vue à retourner
     * @return array
     */
    public function dernieresPrisesDeVue(int $limit = 5): array
    {
        $prisesDeVue = $this->prisesDeVue->toArray();
        
        // Trier par date décroissante (plus récent en premier)
        usort($prisesDeVue, function ($a, $b) {
            if (!$a->getDatePdv() || !$b->getDatePdv()) {
                return 0;
            }
            return $b->getDatePdv() <=> $a->getDatePdv();
        });
        
        return array_slice($prisesDeVue, 0, $limit);
    }

    /**
     * Récupère la dernière prise de vue (la plus récente)
     * 
     * @return PriseDeVue|null
     */
    public function getDernierePriseDeVue(): ?PriseDeVue
    {
        $dernieres = $this->dernieresPrisesDeVue(1);
        return !empty($dernieres) ? $dernieres[0] : null;
    }

    /**
     * Calcule le nombre total d'élèves photographiés dans cette école
     * 
     * @return int
     */
    public function getTotalElevesPhotographies(): int
    {
        $total = 0;
        foreach ($this->prisesDeVue as $priseDeVue) {
            $total += $priseDeVue->getNbEleves() ?? 0;
        }
        return $total;
    }

    /**
     * Calcule le chiffre d'affaires total pour l'école
     * 
     * @return float
     */
    public function getChiffreAffairesTotalEcole(): float
    {
        $total = 0.0;
        foreach ($this->prisesDeVue as $priseDeVue) {
            $total += (float)($priseDeVue->getPrixEcole() ?? 0);
        }
        return $total;
    }

    /**
     * Calcule le chiffre d'affaires total pour les parents
     * 
     * @return float
     */
    public function getChiffreAffairesTotalParents(): float
    {
        $total = 0.0;
        foreach ($this->prisesDeVue as $priseDeVue) {
            $total += (float)($priseDeVue->getPrixParent() ?? 0);
        }
        return $total;
    }

    /**
     * Vérifie si l'école a des prises de vue récentes (dans les 6 derniers mois)
     * 
     * @return bool
     */
    public function hasRecentPrisesDeVue(): bool
    {
        $sixMoisAgo = new \DateTime('-6 months');
        
        foreach ($this->prisesDeVue as $priseDeVue) {
            if ($priseDeVue->getDatePdv() && $priseDeVue->getDatePdv() >= $sixMoisAgo) {
                return true;
            }
        }
        
        return false;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'École';
    }
}