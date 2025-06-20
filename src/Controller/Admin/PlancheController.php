<?php

namespace App\Controller\Admin;

use App\Entity\Planche;
use App\Form\PlancheType;
use App\Repository\PlancheRepository;
use App\Security\Attribute\EntityAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/admin/planche', name: 'admin_planche_')]
#[IsGranted('ROLE_ADMIN')]
final class PlancheController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PlancheRepository $repo): Response
    {
        $qb = $repo->createQueryBuilder('p')->orderBy('p.updatedAt', 'DESC');

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage(10)
              ->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('admin/planche/index.html.twig', [
            'pager' => $pager,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $planche = new Planche();
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($planche);
            $entityManager->flush();

            $this->addFlash('success', 'Planche créée avec succès !');

            return $this->redirectToRoute('admin_planche_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Planche $planche, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_planche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/planche/edit.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Planche $planche, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(EntityAction::DELETE->value, $planche);

        if ($this->isCsrfTokenValid('delete'.$planche->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($planche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_planche_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/modal/new', name: 'modal_new', methods: ['GET', 'POST'])]
    public function modalNew(Request $request, EntityManagerInterface $em): Response
    {
        $planche = new Planche();
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($planche);
                $em->flush();

                return $this->json([
                    'success' => true,
                    'id'      => $planche->getId(),
                    'text'    => $planche->getNom(),
                    'message' => 'Planche créée avec succès',
                ]);
            }

            // Form non valide → on renvoie le HTML avec les erreurs
            $html = $this->renderView('admin/planche/_form.html.twig', [
                'form'         => $form->createView(),
                'button_label' => 'Créer',
            ]);

            return $this->json([
                'success' => false,
                'html'    => $html,
                'errors'  => $form->getErrors(true, false),
            ], 422);
        }

        // Premier affichage (GET) → on rend juste le formulaire
        return $this->render('admin/planche/_form.html.twig', [
            'form'         => $form->createView(),
            'button_label' => 'Créer',
        ]);
    }
}
