<?php

namespace App\Service;

use App\Entity\PriseDeVue;
use App\Entity\User;
use App\Repository\PriseDeVueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service gérant les opérations sur les prises de vue
 */
class PriseDeVueManager
{
    private EntityManagerInterface $entityManager;
    private PriseDeVueRepository $priseDeVueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PriseDeVueRepository $priseDeVueRepository
    ) {
        $this->entityManager = $entityManager;
        $this->priseDeVueRepository = $priseDeVueRepository;
    }

    /**
     * Récupère les prises de vue selon des critères avec pagination
     */
    public function findByCriteriaPaginated(array $criteria, int $page = 1, int $limit = 10): array
    {
        return $this->priseDeVueRepository->findByCriteriaPaginated($criteria, $page, $limit);
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
    public function updateComment(PriseDeVue $priseDeVue, string $commentaire): PriseDeVue
    {
        $priseDeVue->setCommentaire($commentaire);
        $this->entityManager->flush();
        
        return $priseDeVue;
    }

    /**
     * Supprime une prise de vue
     */
    public function delete(PriseDeVue $priseDeVue): void
    {
        $this->entityManager->remove($priseDeVue);
        $this->entityManager->flush();
    }

    /**
     * Clone une prise de vue existante
     */
    public function clonePriseDeVue(PriseDeVue $original): PriseDeVue
    {
        $clone = new PriseDeVue();
        $clone->setEcole($original->getEcole());
        $clone->setDate(new \DateTime());
        $clone->setPhotographe($original->getPhotographe());
        $clone->setNbEleves($original->getNbEleves());
        $clone->setNbClasses($original->getNbClasses());
        $clone->setClasses($original->getClasses());
        $clone->setTypePrise($original->getTypePrise());
        $clone->setTypeVente($original->getTypeVente());
        $clone->setTheme($original->getTheme());
        $clone->setPrixEcole($original->getPrixEcole());
        $clone->setPrixParents($original->getPrixParents());
        
        // Cloner les relations many-to-many
        foreach ($original->getPlanchesIndividuelles() as $planche) {
            $clone->addPlanchesIndividuelle($planche);
        }
        
        foreach ($original->getPlanchesFratries() as $planche) {
            $clone->addPlanchesFratry($planche);
        }
        
        $clone->setCommentaire("Clonée depuis la prise de vue du " . $original->getDate()->format('d/m/Y'));
        
        $this->entityManager->persist($clone);
        $this->entityManager->flush();
        
        return $clone;
    }

    /**
     * Calcule les prix totaux d'une prise de vue
     */
    public function calculateTotalPrices(PriseDeVue $priseDeVue): array
    {
        $prixTotalEcole = $priseDeVue->getPrixEcole();
        $prixTotalParents = $priseDeVue->getPrixParents();
        
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
            'prixTotalParents' => $prixTotalParents
        ];
    }
}