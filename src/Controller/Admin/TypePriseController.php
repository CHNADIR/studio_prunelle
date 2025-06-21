<?php

namespace App\Controller\Admin;

use App\Entity\TypePrise;
use App\Form\TypePriseType;
use App\Repository\TypePriseRepository;
use App\Service\ReferentialManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/type-prise')]
#[IsGranted('ROLE_ADMIN')]
class TypePriseController extends AbstractReferentialController
{
    private ReferentialManager $referentialManager;

    public function __construct(ReferentialManager $referentialManager)
    {
        $this->referentialManager = $referentialManager;
    }

    #[Route('/', name: 'admin_type_prise_index', methods: ['GET'])]
    public function index(TypePriseRepository $typePriseRepository): Response
    {
        return $this->render('admin/type_prise/index.html.twig', [
            'type_prises' => $typePriseRepository->findAllActive(),
        ]);
    }

    #[Route('/new', name: 'admin_type_prise_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $typePrise = new TypePrise();
        $typePrise->setActive(true);
        
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($typePrise);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de prise a été créé avec succès.');
                return $this->redirectToRoute('admin_type_prise_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/type_prise/new.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modal-new', name: 'admin_type_prise_modal_new', methods: ['GET', 'POST'])]
    public function modalNew(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $typePrise = new TypePrise();
        $typePrise->setActive(true);
        
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        return $this->handleModalNew(
            $request,
            $entityManager,
            $validator,
            $typePrise,
            $form,
            'admin/type_prise/modal_new.html.twig',
            'Le type de prise a été créé avec succès.',
            'admin_type_prise_index'
        );
    }

    #[Route('/{id}', name: 'admin_type_prise_show', methods: ['GET'])]
    public function show(TypePrise $typePrise): Response
    {
        return $this->render('admin/type_prise/show.html.twig', [
            'type_prise' => $typePrise,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_type_prise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypePrise $typePrise): Response
    {
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($typePrise);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de prise a été modifié avec succès.');
                return $this->redirectToRoute('admin_type_prise_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/type_prise/edit.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_prise_delete', methods: ['POST'])]
    public function delete(Request $request, TypePrise $typePrise): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typePrise->getId(), $request->request->get('_token'))) {
            // Vérifier si le type de prise peut être supprimé
            if (!$this->referentialManager->canDelete($typePrise, 'prisesDeVue')) {
                $this->addFlash('error', 'Ce type de prise ne peut pas être supprimé car il est utilisé par des prises de vue.');
                return $this->redirectToRoute('admin_type_prise_index');
            }
            
            $result = $this->referentialManager->delete($typePrise);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de prise a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        }

        return $this->redirectToRoute('admin_type_prise_index');
    }
}