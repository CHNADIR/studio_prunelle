<?php

namespace App\Service;

use App\Entity\Ecole;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de gestion des écoles selon les patterns recommandés
 * Pattern appliqué: Service Layer Pattern (patterns.md)
 * Responsabilité: Validation métier et persistance des écoles
 */
class EcoleManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EcoleRepository $ecoleRepository,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    /**
     * Sauvegarde une école avec validation métier
     * Pattern: Service Layer - validation centralisée
     */
    public function save(Ecole $ecole): array
    {
        try {
            // Validation avec contraintes Symfony
            $violations = $this->validator->validate($ecole);
            
            if (count($violations) > 0) {
                return [
                    'success' => false,
                    'errors' => $violations
                ];
            }

            // Validation métier spécifique aux écoles
            $businessValidation = $this->validateBusinessRules($ecole);
            if (!$businessValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $businessValidation['errors']
                ];
            }

            $isNew = $ecole->getId() === null;
            
            if ($isNew) {
                $this->entityManager->persist($ecole);
            }
            
            $this->entityManager->flush();

            $this->logger->info('École sauvegardée', [
                'id' => $ecole->getId(),
                'nom' => $ecole->getNom(),
                'code' => $ecole->getCode(),
                'is_new' => $isNew
            ]);

            return [
                'success' => true, 
                'entity' => $ecole,
                'is_new' => $isNew
            ];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la sauvegarde de l\'école', [
                'error' => $e->getMessage(),
                'ecole_nom' => $ecole->getNom() ?? 'N/A'
            ]);

            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Supprime une école avec vérification d'usage
     * Pattern: Service Layer - règles métier centralisées
     */
    public function delete(Ecole $ecole): array
    {
        try {
            // Vérifier que l'école peut être supprimée
            if (!$this->canDelete($ecole)) {
                return [
                    'success' => false,
                    'errors' => ['Cette école ne peut pas être supprimée car elle a des prises de vue associées.']
                ];
            }

            $this->entityManager->remove($ecole);
            $this->entityManager->flush();

            $this->logger->info('École supprimée', [
                'id' => $ecole->getId(),
                'nom' => $ecole->getNom()
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de l\'école', [
                'error' => $e->getMessage(),
                'ecole_id' => $ecole->getId()
            ]);

            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Vérifie si une école peut être supprimée
     * Pattern: Service Layer - logique métier centralisée
     */
    public function canDelete(Ecole $ecole): bool
    {
        // Vérifier s'il y a des prises de vue associées
        return $ecole->getPrisesDeVue()->isEmpty();
    }

    /**
     * Création d'une nouvelle école avec validation
     */
    public function createNew(): Ecole
    {
        $ecole = new Ecole();
        $ecole->setActive(true); // Par défaut active
        
        return $ecole;
    }

    /**
     * Recherche d'écoles avec critères
     * Pattern: Service Layer - orchestration Repository
     */
    public function findByCriteria(array $criteria, int $page = 1, int $limit = 10): array
    {
        return $this->ecoleRepository->findByCriteriaWithPagination(
            $criteria, 
            $page, 
            $limit, 
            $criteria['sort'] ?? 'nom', 
            $criteria['order'] ?? 'asc'
        );
    }

    /**
     * Obtient les statistiques des écoles
     * Pattern: Service Layer - logique métier
     */
    public function getStats(): array
    {
        $total = $this->ecoleRepository->count([]);
        $actives = $this->ecoleRepository->count(['active' => true]);
        $inactives = $total - $actives;

        return [
            'total' => $total,
            'actives' => $actives,
            'inactives' => $inactives,
            'avec_prises_de_vue' => $this->ecoleRepository->countWithPrisesDeVue()
        ];
    }

    /**
     * Validation des règles métier pour les écoles
     * Pattern: Validation centralisée avec règles métier
     */
    private function validateBusinessRules(Ecole $ecole): array
    {
        $errors = [];

        // Vérifier l'unicité du code école
        if ($ecole->getCode()) {
            $existingEcole = $this->ecoleRepository->findOneBy(['code' => $ecole->getCode()]);
            if ($existingEcole && $existingEcole->getId() !== $ecole->getId()) {
                $errors[] = 'Une école avec ce code existe déjà.';
            }
        }

        // Vérifier la longueur du code (5 caractères)
        if ($ecole->getCode() && strlen($ecole->getCode()) !== 5) {
            $errors[] = 'Le code école doit faire exactement 5 caractères.';
        }

        // Vérifier l'unicité du nom dans la même ville
        if ($ecole->getNom() && $ecole->getVille()) {
            $existingEcole = $this->ecoleRepository->findOneBy([
                'nom' => $ecole->getNom(),
                'ville' => $ecole->getVille()
            ]);
            if ($existingEcole && $existingEcole->getId() !== $ecole->getId()) {
                $errors[] = 'Une école avec ce nom existe déjà dans cette ville.';
            }
        }

        // Validation du code postal français
        if ($ecole->getCodePostal() && !preg_match('/^\d{5}$/', $ecole->getCodePostal())) {
            $errors[] = 'Le code postal doit être composé de 5 chiffres.';
        }

        // Validation du téléphone français
        if ($ecole->getTelephone() && !preg_match('/^(\+33|0)[1-9](\d{8})$/', str_replace(' ', '', $ecole->getTelephone()))) {
            $errors[] = 'Le numéro de téléphone n\'est pas valide.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
} 