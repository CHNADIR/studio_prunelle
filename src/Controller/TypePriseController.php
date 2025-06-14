<?php

namespace App\Controller;

use App\Entity\TypePrise;
use App\Form\TypePriseForm;
use App\Repository\TypePriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; // Import PaginatorInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Import Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/prise')]
final class TypePriseController extends AbstractController
{
    #[Route(name: 'app_type_prise_index', methods: ['GET'])]
    public function index(
        TypePriseRepository $typePriseRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $typePriseRepository->createQueryBuilder('tp');

        $search = $request->query->get('search');
        if ($search) {
            $queryBuilder->andWhere('tp.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10, // Limite par page
            ['defaultSortFieldName' => 'tp.nom', 'defaultSortDirection' => 'asc']
        );

        return $this->render('type_prise/index.html.twig', [
            'type_prises' => $pagination,
            'current_search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_type_prise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typePrise = new TypePrise();
        $form = $this->createForm(TypePriseForm::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typePrise);
            $entityManager->flush();
            $this->addFlash('success', 'Type de prise créé avec succès.');
            return $this->redirectToRoute('app_type_prise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_prise/new.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_prise_show', methods: ['GET'])]
    public function show(TypePrise $typePrise): Response
    {
        return $this->render('type_prise/show.html.twig', [
            'type_prise' => $typePrise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_prise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypePrise $typePrise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypePriseForm::class, $typePrise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Type de prise modifié avec succès.');
            return $this->redirectToRoute('app_type_prise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_prise/edit.html.twig', [
            'type_prise' => $typePrise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_prise_delete', methods: ['POST'])]
    public function delete(Request $request, TypePrise $typePrise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typePrise->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typePrise);
            $entityManager->flush();
            $this->addFlash('success', 'Type de prise supprimé avec succès.');
        }

        return $this->redirectToRoute('app_type_prise_index', [], Response::HTTP_SEE_OTHER);
    }
}
