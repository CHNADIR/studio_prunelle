<?php

namespace App\Controller\Admin;

use App\Entity\Planche;
use App\Form\PlancheType;
use App\Repository\PlancheRepository;
use App\Service\ReferentialManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/admin/planche', name: 'admin_planche_')]
#[IsGranted('ROLE_ADMIN')]
final class PlancheController extends AbstractReferentialController
{
    private ReferentialManager $referentialManager;

    public function __construct(ReferentialManager $referentialManager)
    {
        $this->referentialManager = $referentialManager;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PlancheRepository $repo): Response
    {
        $qb = $repo->createQueryBuilder('p')->orderBy('p.updatedAt', 'DESC');

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage(10)
              ->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('admin/planche/index.html.twig', [
            'pager' => $pager,
            'planches' => $pager->getCurrentPageResults(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $planche = new Planche();
        $planche->setActif(true);
        
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($planche);
            
            if ($result['success']) {
                $this->addFlash('success', 'La planche a été créée avec succès.');
                return $this->redirectToRoute('admin_planche_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/planche/new.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Planche $planche): Response
    {
        return $this->render('admin/planche/show.html.twig', [
            'planche' => $planche,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Planche $planche): Response
    {
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($planche);
            
            if ($result['success']) {
                $this->addFlash('success', 'La planche a été modifiée avec succès.');
                return $this->redirectToRoute('admin_planche_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/planche/edit.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Planche $planche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$planche->getId(), $request->request->get('_token'))) {
            if (!$this->referentialManager->canDelete($planche, 'prisesDeVue')) {
                $this->addFlash('error', 'Cette planche ne peut pas être supprimée car elle est utilisée par des prises de vue.');
                return $this->redirectToRoute('admin_planche_index');
            }
            
            $result = $this->referentialManager->delete($planche);
            
            if ($result['success']) {
                $this->addFlash('success', 'La planche a été supprimée avec succès.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        }

        return $this->redirectToRoute('admin_planche_index');
    }

    #[Route('/modal-new', name: 'modal_new', methods: ['GET', 'POST'])]
    public function modalNew(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $planche = new Planche();
        $planche->setActif(true);
        
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        return $this->handleModalNew(
            $request,
            $entityManager,
            $validator,
            $planche,
            $form,
            'admin/planche/modal_new.html.twig',
            'La planche a été créée avec succès.',
            'admin_planche_index'
        );
    }
}
