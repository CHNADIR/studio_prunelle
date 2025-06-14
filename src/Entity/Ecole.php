<?php

namespace App\Entity;

use App\Repository\EcoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EcoleRepository::class)]
class Ecole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 5, nullable: true, unique: true)] // Ajout de unique: true ici si le code doit être unique en BDD
    #[Assert\NotBlank(message: "Le code de l'école ne peut pas être vide.")]
    #[Assert\Length(max: 5, maxMessage: "Le code ne doit pas dépasser {{ limit }} caractères.")]
    // Si vous voulez une contrainte d'unicité validée par Symfony (en plus de celle de la BDD) :
    // #[Assert\UniqueEntity(fields: ['code'], message: "Ce code est déjà utilisé.")] // Nécessite d'ajouter UniqueEntity au use et à la classe
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le genre ne peut pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le genre ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $genre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'école ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La rue ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "La rue ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $rue = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La ville ne peut pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "La ville ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le code postal ne peut pas être vide.")]
    #[Assert\Length(max: 10, maxMessage: "Le code postal ne doit pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[0-9]{5}$/", message: "Le code postal doit être composé de 5 chiffres.")] // Exemple pour code postal français
    private ?string $codePostal = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: "Le numéro de téléphone ne doit pas dépasser {{ limit }} caractères.")]
    // #[Assert\Regex(pattern: "/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/", message: "Format de téléphone invalide.")] // Exemple pour téléphone français
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas une adresse email valide.")]
    #[Assert\Length(max: 100, maxMessage: "L'email ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $email = null; // Déjà présent dans votre code, juste ajout de Length

    #[ORM\Column]
    #[Assert\NotNull(message: "Le statut actif doit être défini.")] // Pour les booléens, NotNull est plus approprié que NotBlank
    private ?bool $active = null; // Déjà présent, juste ajout de NotNull

    /**
     * @var Collection<int, PriseDeVue>
     */
    #[ORM\OneToMany(targetEntity: PriseDeVue::class, mappedBy: 'ecole')]
    #[Assert\Valid] // Pour valider chaque PriseDeVue dans la collection
    private Collection $priseDeVues;

    public function __construct()
    {
        $this->priseDeVues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, PriseDeVue>
     */
    public function getPriseDeVues(): Collection
    {
        return $this->priseDeVues;
    }

    public function addPriseDeVue(PriseDeVue $priseDeVue): static
    {
        if (!$this->priseDeVues->contains($priseDeVue)) {
            $this->priseDeVues->add($priseDeVue);
            $priseDeVue->setEcole($this);
        }

        return $this;
    }

    public function removePriseDeVue(PriseDeVue $priseDeVue): static
    {
        if ($this->priseDeVues->removeElement($priseDeVue)) {
            // set the owning side to null (unless already changed)
            if ($priseDeVue->getEcole() === $this) {
                $priseDeVue->setEcole(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
