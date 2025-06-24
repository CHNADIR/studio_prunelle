<?php

namespace App\Controller\Admin;

use App\Entity\Ecole;
use App\Form\EcoleType;
use App\Security\Voter\EcoleVoter;
use App\Service\EcoleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ecole')]
#[IsGranted('ROLE_ADMIN')]
class EcoleController extends AbstractController
{
    public function __construct(
        private readonly EcoleManager $ecoleManager
    ) {}

    #[Route('/', name: 'admin_ecole_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'nom');
        $sortOrder = $request->query->get('order', 'asc');
        $status = $request->query->get('status', 'all'); 

        $criteria = [];
        if (!empty($search)) {
            $criteria['search'] = $search;
        }
        if ($status !== 'all') {
            $criteria['active'] = ($status === 'active');
        }
        $criteria['sort'] = $sortBy;
        $criteria['order'] = $sortOrder;

        $data = $this->ecoleManager->findByCriteria($criteria, $page, $limit);

        return $this->render('admin/ecole/index.html.twig', [
            'ecoles' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => $limit,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'status' => $status,
        ]);
    }

    #[Route('/new', name: 'admin_ecole_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $ecole = $this->ecoleManager->createNew();
        $form = $this->createForm(EcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->ecoleManager->save($ecole);
            
            if ($result['success']) {
                $this->addFlash('success', 'L\'école a été créée avec succès.');
                return $this->redirectToRoute('admin_ecole_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/ecole/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_show', methods: ['GET'])]
    #[IsGranted(EcoleVoter::ECOLE_VIEW, subject: 'ecole')]
    public function show(Ecole $ecole): Response
    {
        return $this->render('admin/ecole/show.html.twig', [
            'ecole' => $ecole,
            'can_delete' => $this->ecoleManager->canDelete($ecole),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ecole_edit', methods: ['GET','POST'])]
    #[IsGranted(EcoleVoter::ECOLE_EDIT, subject: 'ecole')]
    public function edit(Request $request, Ecole $ecole): Response
    {
        $form = $this->createForm(EcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->ecoleManager->save($ecole);
            
            if ($result['success']) {
                $this->addFlash('success', 'L\'école a été modifiée avec succès.');
                return $this->redirectToRoute('admin_ecole_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_delete', methods: ['POST'])]
    #[IsGranted(EcoleVoter::ECOLE_DELETE, subject: 'ecole')]
    public function delete(Request $request, Ecole $ecole): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ecole->getId(), $request->request->get('_token'))) {
            $result = $this->ecoleManager->delete($ecole);
            
            if ($result['success']) {
                $this->addFlash('success', 'L\'école a été supprimée avec succès.');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('danger', $error);
                }
            }
        }

        return $this->redirectToRoute('admin_ecole_index');
    }
}