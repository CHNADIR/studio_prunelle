<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCrudController;
use App\Controller\Abstract\AbstractCrudControllerFix; // Importez le trait
use App\Entity\TypeVente;
use App\Form\TypeVenteForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/vente')]
final class TypeVenteController extends AbstractCrudController
{
    use AbstractCrudControllerFix; // Utilisez le trait correctement

    protected function getEntityClass(): string
    {
        return TypeVente::class;
    }

    protected function getFormClass(): string
    {
        return TypeVenteForm::class;
    }

    protected function getIndexRouteName(): string
    {
        return 'app_type_vente_index';
    }

    protected function getTemplateBasePath(): string
    {
        return 'type_vente';
    }

    protected function getEntityNameSingular(): string
    {
        return 'type de vente';
    }

    protected function getEntityNamePlural(): string
    {
        return 'Type Ventes'; // Modifié pour correspondre au template après transformation
    }

    // Surcharger pour correspondre à l'alias utilisé dans les templates KNP Paginator
    protected function getEntityAlias(): string
    {
        return 'tv';
    }

    #[Route(name: 'app_type_vente_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // La sécurité est gérée par security.yaml ou un Voter si configuré
        return $this->processIndex($request);
    }

    #[Route('/new', name: 'app_type_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->processNew($request);
    }

    #[Route('/{id}', name: 'app_type_vente_show', requirements: ['id' => '\d+'], methods: ['GET'])]
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
            'type_vente' => $entity, // Utilisation explicite du nom 'type_vente'
            'edit_route_name' => 'app_type_vente_edit',
            'delete_route_name' => 'app_type_vente_delete',
            'index_route_name' => 'app_type_vente_index',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_vente_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
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
            'type_vente' => $entity, // Utilisation explicite du nom 'type_vente'
            'form' => $form->createView(),
            'entity_name_singular' => $this->getEntityNameSingular(),
            'entity_name_plural' => $this->getEntityNamePlural(),
            'index_route_name' => $this->getIndexRouteName(),
            'is_new' => false,
        ]);
    }

    #[Route('/{id}', name: 'app_type_vente_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->processDelete($request, $id);
    }
}
