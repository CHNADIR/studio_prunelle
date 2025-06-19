<?php

namespace App\Admin\Controller;

use App\Entity\Planche;
use App\Form\PlancheType;
use App\Repository\PlancheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/planche', name: 'admin_planche_')]
#[IsGranted('ROLE_ADMIN')]
final class PlancheController extends AbstractController
{
    #[Route(name: 'app_planche_index', methods: ['GET'])]
    public function index(PlancheRepository $plancheRepository): Response
    {
        return $this->render('planche/index.html.twig', [
            'planches' => $plancheRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_planche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $planche = new Planche();
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($planche);
            $entityManager->flush();

            return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planche/new.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_planche_show', methods: ['GET'])]
    public function show(Planche $planche): Response
    {
        return $this->render('planche/show.html.twig', [
            'planche' => $planche,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_planche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Planche $planche, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlancheType::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planche/edit.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_planche_delete', methods: ['POST'])]
    public function delete(Request $request, Planche $planche, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$planche->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($planche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
    }
}
