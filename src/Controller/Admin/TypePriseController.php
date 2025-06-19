<?php

namespace App\Controller\Admin;

use App\Entity\TypePrise;
use App\Form\TypePriseType;
use App\Repository\TypePriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/type-prise')]
class TypePriseController extends AbstractController
{
    #[Route('/', name: 'admin_type_prise_index', methods: ['GET'])]
    public function index(TypePriseRepository $typePriseRepository): Response
    {
        return $this->render('admin/type_prise/index.html.twig', [
            'type_prises' => $typePriseRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_type_prise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typePrise = new TypePrise();
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typePrise);
            $entityManager->flush();

            $this->addFlash('success', 'Le type de prise a été créé avec succès.');
            return $this->redirectToRoute('admin_type_prise_index');
        }

        return $this->render('admin/type_prise/new.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modal-new', name: 'admin_type_prise_modal_new', methods: ['GET', 'POST'])]
    public function modalNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typePrise = new TypePrise();
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typePrise);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'id' => $typePrise->getId(),
                    'text' => $typePrise->getNom(),
                ]);
            }

            $this->addFlash('success', 'Le type de prise a été créé avec succès.');
            return $this->redirectToRoute('admin_type_prise_index');
        }

        return $this->render('admin/type_prise/modal_new.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_prise_show', methods: ['GET'])]
    public function show(TypePrise $typePrise): Response
    {
        return $this->render('admin/type_prise/show.html.twig', [
            'type_prise' => $typePrise,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_type_prise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypePrise $typePrise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypePriseType::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le type de prise a été modifié avec succès.');
            return $this->redirectToRoute('admin_type_prise_index');
        }

        return $this->render('admin/type_prise/edit.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_prise_delete', methods: ['POST'])]
    public function delete(Request $request, TypePrise $typePrise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typePrise->getId(), $request->request->get('_token'))) {
            // Vérifier si le type de prise est utilisé par des prises de vue
            if (!$typePrise->getPrisesDeVue()->isEmpty()) {
                $this->addFlash('error', 'Ce type de prise ne peut pas être supprimé car il est utilisé par des prises de vue.');
                return $this->redirectToRoute('admin_type_prise_index');
            }

            $entityManager->remove($typePrise);
            $entityManager->flush();
            $this->addFlash('success', 'Le type de prise a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_type_prise_index');
    }
}