<?php

namespace App\Service;

use App\Entity\PriseDeVue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service gérant les opérations sur les prises de vue
 */
class PriseDeVueManager
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * Sauvegarde une prise de vue
     * 
     * @param PriseDeVue $priseDeVue Prise de vue à sauvegarder
     * @return array Résultat de l'opération
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
        
        try {
            $isNew = $priseDeVue->getId() === null;
            
            // Persister seulement si l'entité est nouvelle
            if ($isNew) {
                $this->entityManager->persist($priseDeVue);
            }
            
            $this->entityManager->flush();
            
            return [
                'success' => true,
                'entity' => $priseDeVue,
                'is_new' => $isNew
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Supprime une prise de vue
     * 
     * @param PriseDeVue $priseDeVue Prise de vue à supprimer
     * @return array Résultat de l'opération
     */
    public function delete(PriseDeVue $priseDeVue): array
    {
        try {
            $this->entityManager->remove($priseDeVue);
            $this->entityManager->flush();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Clone une prise de vue existante
     * 
     * @param PriseDeVue $original Prise de vue à cloner
     * @return PriseDeVue Nouveau clone
     */
    public function cloner(PriseDeVue $original): PriseDeVue
    {
        $clone = new PriseDeVue();
        
        // Copier les propriétés simples
        $clone->setDate(new \DateTime());
        $clone->setEcole($original->getEcole());
        $clone->setPhotographe($original->getPhotographe());
        $clone->setTypePrise($original->getTypePrise());
        $clone->setTypeVente($original->getTypeVente());
        $clone->setTheme($original->getTheme());
        $clone->setNbEleves($original->getNbEleves());
        $clone->setNbClasses($original->getNbClasses());
        $clone->setClasses($original->getClasses());
        $clone->setPrixEcole($original->getPrixEcole());
        $clone->setPrixParents($original->getPrixParents());
        
        // Ajouter "(copie)" au commentaire
        $commentaire = $original->getCommentaire();
        if ($commentaire) {
            $clone->setCommentaire($commentaire . ' (copie)');
        }
        
        // Copier les planches individuelles
        foreach ($original->getPlanchesIndividuelles() as $planche) {
            $clone->addPlanchesIndividuelle($planche);
        }
        
        // Copier les planches fratries
        foreach ($original->getPlanchesFratries() as $planche) {
            $clone->addPlanchesFratry($planche);
        }
        
        return $clone;
    }

    /**
     * Calcule le prix total d'une prise de vue
     * 
     * @param PriseDeVue $priseDeVue Prise de vue à calculer
     * @return array Prix total école et parents
     */
    public function calculerPrixTotal(PriseDeVue $priseDeVue): array
    {
        $prixTotalEcole = $priseDeVue->getPrixEcole() ?? 0;
        $prixTotalParents = $priseDeVue->getPrixParents() ?? 0;
        
        // Ajouter le prix des planches individuelles
        foreach ($priseDeVue->getPlanchesIndividuelles() as $planche) {
            $prixTotalEcole += $planche->getPrixEcole();
            $prixTotalParents += $planche->getPrixParents();
        }
        
        // Ajouter le prix des planches fratries
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