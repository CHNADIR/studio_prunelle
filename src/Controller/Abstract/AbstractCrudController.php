<?php

namespace App\Controller\Abstract;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractCrudController extends SymfonyAbstractController
{
    protected EntityManagerInterface $entityManager;
    protected PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
    }

    // --- Méthodes abstraites à implémenter par les classes enfants ---
    abstract protected function getEntityClass(): string;
    abstract protected function getFormClass(): string;
    abstract protected function getIndexRouteName(): string;
    abstract protected function getTemplateBasePath(): string; // ex: 'theme' pour le dossier templates/theme/
    abstract protected function getEntityNameSingular(): string; // ex: 'thème'
    abstract protected function getEntityNamePlural(): string; // ex: 'thèmes'

    // --- Configuration optionnelle (peut être surchargée) ---
    protected function getSearchableFields(): array
    {
        return ['nom']; // Par défaut, recherche sur le champ 'nom'
    }

    protected function getDefaultSortField(): string
    {
        return 'nom'; // Par défaut, tri sur le champ 'nom'
    }

    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    protected function getItemsPerPage(): int
    {
        return 10;
    }

    // --- Méthodes CRUD protégées (appelées par les actions publiques des enfants) ---

    protected function processIndex(Request $request): Response
    {
        $repository = $this->getRepository();
        $queryBuilder = $repository->createQueryBuilder($this->getEntityAlias());

        $this->applySearchParameters($queryBuilder, $request);
        $this->applySortParameters($queryBuilder, $request); // KnpPaginator s'en charge aussi, mais on peut pré-configurer

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            $this->getItemsPerPage(),
            [
                'defaultSortFieldName' => $this->getEntityAlias() . '.' . $this->getDefaultSortField(),
                'defaultSortDirection' => $this->getDefaultSortDirection(),
                'sortFieldWhitelist' => $this->getSortFieldWhitelist(),
            ]
        );

        return $this->render($this->getTemplatePath('index'), $this->getRenderParametersForIndex($pagination, $request));
    }

    protected function processNew(Request $request): Response
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();

        $form = $this->createForm($this->getFormClass(), $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->beforePersist($entity, $form, $request);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->afterPersist($entity, $form, $request);

            $this->addFlash('success', ucfirst($this->getEntityNameSingular()) . ' créé(e) avec succès.');
            return $this->redirectToRoute($this->getIndexRouteName());
        }

        return $this->render($this->getTemplatePath('new'), $this->getRenderParametersForNewEdit($form, $entity));
    }

    protected function processShow(int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
        // Vérification simplifiée pour ROLE_ADMIN uniquement
        if (!$this->isGranted('ROLE_ADMIN')) {
            try {
                // Essaie d'utiliser le Voter s'il existe, mais ne bloque pas si ça échoue
                $this->denyAccessUnlessGranted($this->getVoterAttribute('VIEW'), $entity, 'Accès refusé');
            } catch (\Exception $e) {
                // Vérifie au moins le rôle ROLE_GESTION_REFERENTIELS pour les référentiels
                if (!$this->isGranted('ROLE_GESTION_REFERENTIELS')) {
                    throw $this->createAccessDeniedException('Accès refusé');
                }
            }
        }

        return $this->render($this->getTemplatePath('show'), $this->getRenderParametersForShow($entity));
    }

    protected function processEdit(Request $request, int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
        // Vérification simplifiée
        if (!$this->isGranted('ROLE_ADMIN')) {
            try {
                $this->denyAccessUnlessGranted($this->getVoterAttribute('EDIT'), $entity, 'Accès refusé');
            } catch (\Exception $e) {
                if (!$this->isGranted('ROLE_GESTION_REFERENTIELS')) {
                    throw $this->createAccessDeniedException('Accès refusé');
                }
            }
        }

        $form = $this->createForm($this->getFormClass(), $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->beforeUpdate($entity, $form, $request);
            $this->entityManager->flush();
            $this->afterUpdate($entity, $form, $request);

            $this->addFlash('success', ucfirst($this->getEntityNameSingular()) . ' modifié(e) avec succès.');
            return $this->redirectToRoute($this->getIndexRouteName());
        }

        return $this->render($this->getTemplatePath('edit'), $this->getRenderParametersForNewEdit($form, $entity));
    }

    protected function processDelete(Request $request, int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
        // Vérification simplifiée
        if (!$this->isGranted('ROLE_ADMIN')) {
            try {
                $this->denyAccessUnlessGranted($this->getVoterAttribute('DELETE'), $entity, 'Accès refusé');
            } catch (\Exception $e) {
                if (!$this->isGranted('ROLE_GESTION_REFERENTIELS')) {
                    throw $this->createAccessDeniedException('Accès refusé');
                }
            }
        }

        if ($this->isCsrfTokenValid('delete'.$entity->getId(), $request->getPayload()->getString('_token'))) {
            $this->beforeRemove($entity, $request);
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $this->afterRemove($entity, $request);
            $this->addFlash('success', ucfirst($this->getEntityNameSingular()) . ' supprimé(e) avec succès.');
        }

        return $this->redirectToRoute($this->getIndexRouteName());
    }

    // --- Méthodes utilitaires protégées ---

    protected function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository($this->getEntityClass());
    }

    protected function findEntityOrThrowNotFound(int $id): object
    {
        $entity = $this->getRepository()->find($id);
        if (!$entity) {
            throw $this->createNotFoundException(ucfirst($this->getEntityNameSingular()) . ' non trouvé(e) avec l\'ID: ' . $id);
        }
        return $entity;
    }

    protected function getEntityAlias(): string
    {
        $parts = explode('\\', $this->getEntityClass());
        return strtolower(substr(end($parts), 0, 1));
    }

    protected function getTemplatePath(string $action): string
    {
        return $this->getTemplateBasePath() . '/' . $action . '.html.twig';
    }

    protected function getTemplateVariableNameSingular(): string
    {
        // Convertit "type de vente" en "type_de_vente"
        return str_replace([' ', '-'], '_', strtolower($this->getEntityNameSingular()));
    }

    protected function getTemplateVariableNamePlural(): string
    {
        return str_replace([' ', '-'], '_', strtolower($this->getEntityNamePlural()));
    }

    protected function applySearchParameters(QueryBuilder $queryBuilder, Request $request): void
    {
        $search = $request->query->get('search');
        $searchableFields = $this->getSearchableFields();
        $alias = $this->getEntityAlias();

        if ($search && !empty($searchableFields)) {
            $orX = $queryBuilder->expr()->orX();
            foreach ($searchableFields as $field) {
                $orX->add($queryBuilder->expr()->like($alias . '.' . $field, ':search'));
            }
            $queryBuilder->andWhere($orX)
                ->setParameter('search', '%' . $search . '%');
        }
    }
    
    protected function applySortParameters(QueryBuilder $queryBuilder, Request $request): void
    {
        $sort = $request->query->get('sort', $this->getEntityAlias() . '.' . $this->getDefaultSortField());
        $direction = $request->query->get('direction', $this->getDefaultSortDirection());

        if (in_array($sort, $this->getSortFieldWhitelist())) {
             $queryBuilder->orderBy($sort, $direction);
        } else {
            // Fallback to default sort if provided sort field is not whitelisted
            $queryBuilder->orderBy($this->getEntityAlias() . '.' . $this->getDefaultSortField(), $this->getDefaultSortDirection());
        }
    }

    protected function getSortFieldWhitelist(): array
    {
        // Par défaut, autorise le tri sur les champs de recherche et l'ID
        $alias = $this->getEntityAlias();
        $whitelist = [$alias . '.id'];
        foreach ($this->getSearchableFields() as $field) {
            $whitelist[] = $alias . '.' . $field;
        }
        // Ajoutez ici d'autres champs si nécessaire pour chaque entité via surcharge
        return array_unique($whitelist);
    }

    // --- Méthodes pour les paramètres de rendu (peuvent être surchargées) ---
    protected function getRenderParametersForIndex(object $pagination, Request $request): array
    {
        return [
            $this->getTemplateVariableNamePlural() => $pagination,
            'current_search' => $request->query->get('search'),
            'entity_name_singular' => $this->getEntityNameSingular(),
            'entity_name_plural' => $this->getEntityNamePlural(),
            'index_route_name' => $this->getIndexRouteName(),
            'new_route_name' => str_replace('_index', '_new', $this->getIndexRouteName()),
            'show_route_name_base' => str_replace('_index', '_show', $this->getIndexRouteName()), // Pour les liens show
            'edit_route_name_base' => str_replace('_index', '_edit', $this->getIndexRouteName()), // Pour les liens edit
            'delete_route_name_base' => str_replace('_index', '_delete', $this->getIndexRouteName()), // Pour les formulaires delete
        ];
    }

    protected function getRenderParametersForNewEdit(FormInterface $form, object $entity): array
    {
        $isNew = $entity->getId() === null; // Ou une autre logique pour déterminer si c'est une nouvelle entité
        $params = [
            $this->getTemplateVariableNameSingular() => $entity,
            'form' => $form->createView(),
            'entity_name_singular' => $this->getEntityNameSingular(),
            'index_route_name' => $this->getIndexRouteName(),
            'is_new' => $isNew,
        ];

        if (!$isNew && method_exists($entity, 'getId') && $entity->getId() !== null) {
            $params['delete_route_name'] = str_replace('_index', '_delete', $this->getIndexRouteName());
        }

        return $params;
    }

    protected function getRenderParametersForShow(object $entity): array
    {
        return [
            $this->getTemplateVariableNameSingular() => $entity,
            'entity_name_singular' => $this->getEntityNameSingular(),
            'index_route_name' => $this->getIndexRouteName(),
            'edit_route_name' => str_replace('_index', '_edit', $this->getIndexRouteName()),
            'delete_route_name' => str_replace('_index', '_delete', $this->getIndexRouteName()), // Assurez-vous que c'est bien le nom de la route pour delete
        ];
    }

    // --- Hooks pour logique spécifique (à surcharger si besoin) ---
    protected function beforePersist(object $entity, FormInterface $form, Request $request): void {}
    protected function afterPersist(object $entity, FormInterface $form, Request $request): void {}
    protected function beforeUpdate(object $entity, FormInterface $form, Request $request): void {}
    protected function afterUpdate(object $entity, FormInterface $form, Request $request): void {}
    protected function beforeRemove(object $entity, Request $request): void {}
    protected function afterRemove(object $entity, Request $request): void {}

    // --- Gestion des Voters (optionnel, à surcharger si les entités ont des voters) ---
    protected function getVoterAttribute(string $action): string
    {
        // Par défaut, pas de vérification de Voter spécifique.
        // Les enfants peuvent surcharger ceci pour retourner 'VIEW', 'EDIT', 'DELETE'
        // si un Voter est associé à l'entité.
        // Exemple: return strtoupper($action); ou une constante de Voter.
        // Pour l'instant, on va supposer que les contrôles d'accès sont faits via security.yaml
        // ou que les contrôleurs enfants surchargent les méthodes processX pour ajouter denyAccessUnlessGranted.
        // Pour une intégration plus poussée, cette méthode devrait être abstraite ou configurée.
        // Pour l'instant, on va la laisser retourner une chaîne qui ne correspondra à aucun attribut de Voter.
        return 'CRUD_'.strtoupper($action).'_'.$this->getEntityAlias(); // Ex: CRUD_VIEW_T
    }
}