<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCrudController;
use App\Controller\Abstract\AbstractCrudControllerFix; // Importez le trait
use App\Entity\TypePrise;
use App\Form\TypePriseForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/prise')]
final class TypePriseController extends AbstractCrudController
{
    use AbstractCrudControllerFix; // Utilisez le trait correctement

    protected function getEntityClass(): string
    {
        return TypePrise::class;
    }

    protected function getFormClass(): string
    {
        return TypePriseForm::class;
    }

    protected function getIndexRouteName(): string
    {
        return 'app_type_prise_index';
    }

    protected function getTemplateBasePath(): string
    {
        return 'type_prise';
    }

    protected function getEntityNameSingular(): string
    {
        return 'type de prise';
    }

    protected function getEntityNamePlural(): string
    {
        return 'Type Prises'; // Modifié pour correspondre au template après transformation
    }

    // Surcharger pour correspondre à l'alias utilisé dans les templates KNP Paginator
    protected function getEntityAlias(): string
    {
        return 'tp';
    }

    #[Route(name: 'app_type_prise_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->processIndex($request);
    }

    #[Route('/new', name: 'app_type_prise_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->processNew($request);
    }

    #[Route('/{id}', name: 'app_type_prise_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $entity = $this->findEntityOrThrowNotFound($id);
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            try {
                $this->denyAccessUnlessGranted($this->getVoterAttribute('VIEW'), $entity, 'Accès refusé');
            } catch (\Exception $e) {
                if (!$this->isGranted('ROLE_GESTION_REFERENTIELS')) {
                    throw $this->createAccessDeniedException('Accès refusé');
                }
            }
        }
        
        return $this->render($this->getTemplatePath('show'), [
            'type_prise' => $entity, // Utilisation explicite du nom 'type_prise'
            'edit_route_name' => 'app_type_prise_edit',
            'delete_route_name' => 'app_type_prise_delete',
            'index_route_name' => 'app_type_prise_index',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_prise_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
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
            'type_prise' => $entity, // Utilisation explicite du nom 'type_prise'
            'form' => $form->createView(),
            'entity_name_singular' => $this->getEntityNameSingular(),
            'entity_name_plural' => $this->getEntityNamePlural(),
            'index_route_name' => $this->getIndexRouteName(),
            'is_new' => false,
        ]);
    }

    #[Route('/{id}', name: 'app_type_prise_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->processDelete($request, $id);
    }
}
