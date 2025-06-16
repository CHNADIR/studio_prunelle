<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCrudController;
use App\Controller\Abstract\AbstractCrudControllerFix; // Importez le trait
use App\Entity\Theme;
use App\Form\ThemeForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/theme')]
final class ThemeController extends AbstractCrudController
{
    use AbstractCrudControllerFix; // Utilisez le trait correctement
    
    protected function getEntityClass(): string
    {
        return Theme::class;
    }

    protected function getFormClass(): string
    {
        return ThemeForm::class;
    }

    protected function getIndexRouteName(): string
    {
        return 'app_theme_index';
    }

    protected function getTemplateBasePath(): string
    {
        return 'theme'; // Correspond au dossier dans templates/theme
    }

    protected function getEntityNameSingular(): string
    {
        return 'thème';
    }

    protected function getEntityNamePlural(): string
    {
        return 'thèmes';
    }

    // Optionnel : surcharger si les champs de recherche ou de tri par défaut sont différents
    /*
    protected function getSearchableFields(): array
    {
        return ['nom', 'description']; // Exemple
    }
    */

    // Les actions publiques appellent les méthodes protégées du parent
    #[Route(name: 'app_theme_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // L'accès à cette route est généralement contrôlé par security.yaml
        // $this->denyAccessUnlessGranted('ROLE_GESTIONNAIRE_REFERENTIEL'); // Exemple
        return $this->processIndex($request);
    }

    #[Route('/new', name: 'app_theme_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_GESTIONNAIRE_REFERENTIEL');
        return $this->processNew($request);
    }

    #[Route('/{id}', name: 'app_theme_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Vérifications d'accès si nécessaire
        }
        
        return $this->render($this->getTemplatePath('show'), [
            'theme' => $entity, // Utilisation explicite du nom 'theme'
            'edit_route_name' => 'app_theme_edit',
            'delete_route_name' => 'app_theme_delete',
            'index_route_name' => 'app_theme_index',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_theme_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
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

        return $this->render($this->getTemplatePath('edit'), [
            'theme' => $entity, // Utilisation explicite du nom 'theme'
            'form' => $form->createView(),
            'edit_route_name' => 'app_theme_edit',
            'delete_route_name' => 'app_theme_delete',
            'index_route_name' => 'app_theme_index',
        ]);
    }

    #[Route('/{id}', name: 'app_theme_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->processDelete($request, $id);
    }

    // Si vous avez un ThemeVoter, vous pouvez surcharger cette méthode :
    /*
    protected function getVoterAttribute(string $action): string
    {
        switch (strtoupper($action)) {
            case 'VIEW':
                return ThemeVoter::VIEW; // Supposant que ThemeVoter::VIEW existe
            case 'EDIT':
                return ThemeVoter::EDIT;
            case 'DELETE':
                return ThemeVoter::DELETE;
        }
        return parent::getVoterAttribute($action); // Fallback
    }
    */
}
