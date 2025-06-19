<?php

namespace App\Service;

use App\Entity\PriseDeVue;
use App\Entity\User;
use App\Repository\PriseDeVueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service gérant les opérations sur les prises de vue
 */
class PriseDeVueManager
{
    private EntityManagerInterface $entityManager;
    private PriseDeVueRepository $priseDeVueRepository;
    private ValidatorInterface $validator;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        PriseDeVueRepository $priseDeVueRepository,
        ValidatorInterface $validator,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->priseDeVueRepository = $priseDeVueRepository;
        $this->validator = $validator;
        $this->security = $security;
    }

    /**
     * Récupère les prises de vue selon des critères avec pagination
     */
    public function findByCriteriaPaginated(array $criteria, int $page = 1, int $limit = 10): array
    {
        // Utiliser la méthode search() au lieu de findByCriteriaPaginated()
        return $this->priseDeVueRepository->search($criteria, $page, $limit);
    }

    /**
     * Récupère les prises de vue d'un photographe
     */
    public function findByPhotographe(User $photographe, int $page = 1, int $limit = 10): array
    {
        return $this->priseDeVueRepository->findByPhotographe($photographe, $page, $limit);
    }

    /**
     * Crée une nouvelle prise de vue
     */
    public function create(PriseDeVue $priseDeVue, User $createdBy): PriseDeVue
    {
        if (!$priseDeVue->getPhotographe()) {
            $priseDeVue->setPhotographe($createdBy);
        }
        
        $this->entityManager->persist($priseDeVue);
        $this->entityManager->flush();
        
        return $priseDeVue;
    }

    /**
     * Met à jour une prise de vue existante
     */
    public function update(PriseDeVue $priseDeVue): PriseDeVue
    {
        $this->entityManager->flush();
        
        return $priseDeVue;
    }

    /**
     * Met à jour uniquement le commentaire d'une prise de vue
     */
    public function updateComment(PriseDeVue $priseDeVue, string $commentaire): array
    {
        $priseDeVue->setCommentaire($commentaire);
        return $this->save($priseDeVue);
    }

    /**
     * Supprime une prise de vue
     */
    public function delete(PriseDeVue $priseDeVue): bool
    {
        try {
            $this->entityManager->remove($priseDeVue);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clone une prise de vue existante
     */
    public function clonePriseDeVue(PriseDeVue $original): PriseDeVue
    {
        $clone = new PriseDeVue();
        $clone->setEcole($original->getEcole());
        $clone->setPhotographe($original->getPhotographe());
        $clone->setDate(new \DateTime());
        $clone->setNbClasses($original->getNbClasses());
        $clone->setNbEleves($original->getNbEleves());
        $clone->setPrixEcole($original->getPrixEcole());
        $clone->setPrixParents($original->getPrixParents());
        $clone->setTypePrise($original->getTypePrise());
        $clone->setTypeVente($original->getTypeVente());
        $clone->setTheme($original->getTheme());
        
        // Cloner les relations avec les planches
        foreach ($original->getPlanchesIndividuelles() as $planche) {
            $clone->addPlanchesIndividuelle($planche);
        }
        
        foreach ($original->getPlanchesFratries() as $planche) {
            $clone->addPlanchesFratrie($planche);
        }
        
        // Persister le clone
        $this->entityManager->persist($clone);
        $this->entityManager->flush();
        
        return $clone;
    }

    /**
     * Sauvegarde d'une prise de vue avec validation
     */
    public function save(PriseDeVue $priseDeVue): array
    {
        // Valider l'entité
        $errors = $this->validator->validate($priseDeVue);
        
        if (count($errors) > 0) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Si c'est une nouvelle prise de vue et pas d'utilisateur assigné
        if ($priseDeVue->getId() === null && $priseDeVue->getPhotographe() === null) {
            /** @var User $user */
            $user = $this->security->getUser();
            $priseDeVue->setPhotographe($user);
        }
        
        // Sauvegarder l'entité
        $this->entityManager->persist($priseDeVue);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'entity' => $priseDeVue
        ];
    }

    /**
     * Calcule les prix totaux d'une prise de vue
     */
    public function calculateTotalPrices(PriseDeVue $priseDeVue): array
    {
        $prixTotalEcole = $priseDeVue->getPrixEcole() ?? 0;
        $prixTotalParents = $priseDeVue->getPrixParents() ?? 0;
        
        // Ajouter les prix des planches individuelles
        foreach ($priseDeVue->getPlanchesIndividuelles() as $planche) {
            $prixTotalEcole += $planche->getPrixEcole();
            $prixTotalParents += $planche->getPrixParents();
        }
        
        // Ajouter les prix des planches fratries
        foreach ($priseDeVue->getPlanchesFratries() as $planche) {
            $prixTotalEcole += $planche->getPrixEcole();
            $prixTotalParents += $planche->getPrixParents();
        }
        
        return [
            'prixTotalEcole' => $prixTotalEcole,
            'prixTotalParents' => $prixTotalParents,
            'prixTotal' => $prixTotalEcole + $prixTotalParents
        ];
    }
}