<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service gérant les opérations communes sur les référentiels
 */
class ReferentialManager
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
     * Sauvegarde une entité de référentiel
     * 
     * @param object $entity Entité à sauvegarder
     * @return array Résultat de l'opération
     */
    public function save(object $entity): array
    {
        // Valider l'entité
        $errors = $this->validator->validate($entity);
        
        if (count($errors) > 0) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        try {
            $isNew = $entity->getId() === null;
            
            // Persister seulement si l'entité est nouvelle
            if ($isNew) {
                $this->entityManager->persist($entity);
            }
            
            $this->entityManager->flush();
            
            return [
                'success' => true,
                'entity' => $entity,
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
     * Supprime une entité de référentiel
     * 
     * @param object $entity Entité à supprimer
     * @return array Résultat de l'opération
     */
    public function delete(object $entity): array
    {
        try {
            $this->entityManager->remove($entity);
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
     * Vérifie si une entité de référentiel peut être supprimée
     * 
     * @param object $entity Entité à vérifier
     * @param string $relationProperty Propriété de relation à vérifier
     * @return bool
     */
    public function canDelete(object $entity, string $relationProperty): bool
    {
        // Vérifier si la méthode existe
        $method = 'get' . ucfirst($relationProperty);
        if (!method_exists($entity, $method)) {
            return true;
        }
        
        // Vérifier si la relation est vide
        $relation = $entity->$method();
        
        // Si c'est une collection
        if ($relation instanceof \Doctrine\Common\Collections\Collection) {
            return $relation->isEmpty();
        }
        
        // Si c'est un objet ou null
        return $relation === null;
    }
}