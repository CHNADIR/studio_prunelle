<?php

namespace App\Controller\Admin;

use App\Entity\TypeVente;
use App\Form\TypeVenteType;
use App\Repository\TypeVenteRepository;
use App\Service\ReferentialManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/type-vente')]
#[IsGranted('ROLE_ADMIN')]
class TypeVenteController extends AbstractReferentialController
{
    private ReferentialManager $referentialManager;

    public function __construct(ReferentialManager $referentialManager)
    {
        $this->referentialManager = $referentialManager;
    }

    #[Route('/', name: 'admin_type_vente_index', methods: ['GET'])]
    public function index(TypeVenteRepository $typeVenteRepository): Response
    {
        return $this->render('admin/type_vente/index.html.twig', [
            'type_ventes' => $typeVenteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_type_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $typeVente = new TypeVente();
        $typeVente->setActive(true);
        
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($typeVente);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de vente a été créé avec succès.');
                return $this->redirectToRoute('admin_type_vente_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/type_vente/new.html.twig', [
            'type_vente' => $typeVente,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modal-new', name: 'admin_type_vente_modal_new', methods: ['GET', 'POST'])]
    public function modalNew(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $typeVente = new TypeVente();
        $typeVente->setActive(true);
        
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        return $this->handleModalNew(
            $request,
            $entityManager,
            $validator,
            $typeVente,
            $form,
            'admin/type_vente/modal_new.html.twig',
            'Le type de vente a été créé avec succès.',
            'admin_type_vente_index'
        );
    }

    #[Route('/{id}', name: 'admin_type_vente_show', methods: ['GET'])]
    public function show(TypeVente $typeVente): Response
    {
        return $this->render('admin/type_vente/show.html.twig', [
            'type_vente' => $typeVente,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_type_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeVente $typeVente): Response
    {
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($typeVente);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de vente a été modifié avec succès.');
                return $this->redirectToRoute('admin_type_vente_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/type_vente/edit.html.twig', [
            'type_vente' => $typeVente,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_vente_delete', methods: ['POST'])]
    public function delete(Request $request, TypeVente $typeVente): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeVente->getId(), $request->request->get('_token'))) {
            // Vérifier si le type de vente peut être supprimé
            if (!$this->referentialManager->canDelete($typeVente, 'prisesDeVue')) {
                $this->addFlash('error', 'Ce type de vente ne peut pas être supprimé car il est utilisé par des prises de vue.');
                return $this->redirectToRoute('admin_type_vente_index');
            }
            
            $result = $this->referentialManager->delete($typeVente);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le type de vente a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        }

        return $this->redirectToRoute('admin_type_vente_index');
    }
}