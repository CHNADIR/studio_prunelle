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
 * Pattern appliqué: Service Layer Pattern (patterns.md)
 * Responsabilité: Logique métier centralisée pour PriseDeVue
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
        // Si aucun critère spécifique, utiliser findAllWithPagination
        if (empty($criteria)) {
            return $this->priseDeVueRepository->findAllWithPagination($page, $limit, ['typePrise', 'theme', 'pochettes']);
        }
        
        // Utiliser la méthode optimisée avec pagination dans le repository
        return $this->priseDeVueRepository->findByCriteriaWithPagination($criteria, $page, $limit);
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
        // Assigner le photographe si non défini
        if (!$priseDeVue->getPhotographe()) {
            $priseDeVue->setPhotographe($createdBy);
        }
        
        // Valider et sauvegarder
        $result = $this->save($priseDeVue);
        
        if (!$result['success']) {
            throw new \InvalidArgumentException('Impossible de créer la prise de vue: ' . implode(', ', $result['errors']));
        }
        
        return $result['entity'];
    }

    /**
     * Duplique une prise de vue existante
     */
    public function duplicatePriseDeVue(PriseDeVue $original): PriseDeVue
    {
        $clone = new PriseDeVue();
        
        // Copier les propriétés de base
        $clone->setEcole($original->getEcole());
        $clone->setPhotographe($original->getPhotographe());
        $clone->setDatePdv(new \DateTime()); // Nouvelle date
        $clone->setNbClasses($original->getNbClasses());
        $clone->setNbEleves($original->getNbEleves());
        $clone->setPrixEcole($original->getPrixEcole());
        $clone->setPrixParent($original->getPrixParent());
        
        // Copier les références (sélections uniques)
        $clone->setTypePrise($original->getTypePrise());
        $clone->setTypeVente($original->getTypeVente());
        $clone->setTheme($original->getTheme());
        $clone->setPochetteIndiv($original->getPochetteIndiv());
        $clone->setPochetteFratrie($original->getPochetteFratrie());
        $clone->setPlanche($original->getPlanche());
        
        // Commentaire personnalisé pour la copie
        $commentaireOriginal = $original->getCommentaire() ?: '';
        $clone->setCommentaire('Copie de la prise de vue du ' . $original->getDatePdv()?->format('d/m/Y') . '. ' . $commentaireOriginal);
        
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
     * Supprime une prise de vue
     */
    public function delete(PriseDeVue $priseDeVue): array
    {
        try {
            $this->entityManager->remove($priseDeVue);
            $this->entityManager->flush();
            
            return [
                'success' => true,
                'message' => 'Prise de vue supprimée avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcule le prix total d'une prise de vue
     */
    public function calculerPrixTotal(PriseDeVue $priseDeVue): array
    {
        $prixEcole = $priseDeVue->getPrixEcole() ? (float)$priseDeVue->getPrixEcole() : 0.0;
        $prixParent = $priseDeVue->getPrixParent() ? (float)$priseDeVue->getPrixParent() : 0.0;
        $total = $prixEcole + $prixParent;
        
        return [
            'prix_ecole' => $prixEcole,
            'prix_parent' => $prixParent,
            'total' => $total,
            'total_formate' => number_format($total, 2, ',', ' ') . ' €'
        ];
    }

    /**
     * Génère un rapport de synthèse pour une prise de vue
     */
    public function genererRapportSynthese(PriseDeVue $priseDeVue): array
    {
        $synthese = [
            'id' => $priseDeVue->getId(),
            'ecole' => [
                'nom' => $priseDeVue->getEcole()?->getNom(),
                'code' => $priseDeVue->getEcole()?->getCode(),
                'ville' => $priseDeVue->getEcole()?->getVille()
            ],
            'photographe' => [
                'nom' => $priseDeVue->getPhotographe()?->getNom(),
                'email' => $priseDeVue->getPhotographe()?->getEmail()
            ],
            'details' => [
                'date' => $priseDeVue->getDatePdv()?->format('d/m/Y'),
                'nb_eleves' => $priseDeVue->getNbEleves(),
                'nb_classes' => $priseDeVue->getNbClasses(),
                'type_prise' => $priseDeVue->getTypePrise()?->getLibelle(),
                'theme' => $priseDeVue->getTheme()?->getLibelle(),
                'type_vente' => $priseDeVue->getTypeVente()?->getLibelle()
            ],
            'materiels' => [
                'planche' => $priseDeVue->getPlanche()?->getLibelle(),
                'pochette_indiv' => $priseDeVue->getPochetteIndiv()?->getLibelle(),
                'pochette_fratrie' => $priseDeVue->getPochetteFratrie()?->getLibelle()
            ],
            'tarification' => $this->calculerPrixTotal($priseDeVue),
            'commentaire' => $priseDeVue->getCommentaire(),
            'metadonnees' => [
                'created_at' => $priseDeVue->getCreatedAt()?->format('d/m/Y H:i'),
                'updated_at' => $priseDeVue->getUpdatedAt()?->format('d/m/Y H:i'),
                'is_complete' => $priseDeVue->isComplete()
            ]
        ];
        
        return $synthese;
    }

    /**
     * Recherche les prises de vue avec des critères flexibles
     */
    public function searchPrisesDeVue(string $query, array $filters = [], int $limit = 20): array
    {
        return $this->priseDeVueRepository->searchByQuery($query, $filters, $limit);
    }

    /**
     * Calcule les statistiques globales des prises de vue
     */
    public function calculateStats(?User $photographe = null): array
    {
        $filters = [];
        if ($photographe) {
            $filters['photographe'] = $photographe;
        }
        
        return [
            'total_prises_de_vue' => $this->priseDeVueRepository->countByCriteria($filters),
            'total_eleves' => $this->priseDeVueRepository->sumElevesByCriteria($filters),
            'ca_total' => $this->priseDeVueRepository->sumChiffreAffaireByCriteria($filters),
            'moyenne_eleves_par_prise' => $this->priseDeVueRepository->avgElevesByCriteria($filters),
            'prises_ce_mois' => $this->priseDeVueRepository->countByPeriod(new \DateTime('first day of this month'), new \DateTime('last day of this month'), $filters)
        ];
    }
}