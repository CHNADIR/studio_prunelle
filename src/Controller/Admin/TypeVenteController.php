<?php

namespace App\Controller\Admin;

use App\Entity\TypeVente;
use App\Form\TypeVenteType;
use App\Repository\TypeVenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/type-vente')]
class TypeVenteController extends AbstractController
{
    #[Route('/', name: 'admin_type_vente_index', methods: ['GET'])]
    public function index(TypeVenteRepository $typeVenteRepository): Response
    {
        return $this->render('admin/type_vente/index.html.twig', [
            'type_ventes' => $typeVenteRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_type_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeVente = new TypeVente();
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeVente);
            $entityManager->flush();

            $this->addFlash('success', 'Le type de vente a été créé avec succès.');
            return $this->redirectToRoute('admin_type_vente_index');
        }

        return $this->render('admin/type_vente/new.html.twig', [
            'type_vente' => $typeVente,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modal-new', name: 'admin_type_vente_modal_new', methods: ['GET', 'POST'])]
    public function modalNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeVente = new TypeVente();
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeVente);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'id' => $typeVente->getId(),
                    'text' => $typeVente->getNom(),
                ]);
            }

            $this->addFlash('success', 'Le type de vente a été créé avec succès.');
            return $this->redirectToRoute('admin_type_vente_index');
        }

        return $this->render('admin/type_vente/modal_new.html.twig', [
            'type_vente' => $typeVente,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_vente_show', methods: ['GET'])]
    public function show(TypeVente $typeVente): Response
    {
        return $this->render('admin/type_vente/show.html.twig', [
            'type_vente' => $typeVente,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_type_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeVente $typeVente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeVenteType::class, $typeVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le type de vente a été modifié avec succès.');
            return $this->redirectToRoute('admin_type_vente_index');
        }

        return $this->render('admin/type_vente/edit.html.twig', [
            'type_vente' => $typeVente,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_type_vente_delete', methods: ['POST'])]
    public function delete(Request $request, TypeVente $typeVente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeVente->getId(), $request->request->get('_token'))) {
            // Vérifier si le type de vente est utilisé par des prises de vue
            if (!$typeVente->getPrisesDeVue()->isEmpty()) {
                $this->addFlash('error', 'Ce type de vente ne peut pas être supprimé car il est utilisé par des prises de vue.');
                return $this->redirectToRoute('admin_type_vente_index');
            }

            $entityManager->remove($typeVente);
            $entityManager->flush();
            $this->addFlash('success', 'Le type de vente a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_type_vente_index');
    }
}