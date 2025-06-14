<?php

namespace App\Controller;

use App\Entity\Ecole;
use App\Form\EcoleForm;
use App\Repository\EcoleRepository;
use App\Repository\PriseDeVueRepository; // AJOUTER CETTE LIGNE
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Security\Voter\EcoleVoter;

#[Route('/ecole')]
class EcoleController extends AbstractController
{
    #[Route('/', name: 'app_ecole_index', methods: ['GET'])]
    public function index(
        EcoleRepository $ecoleRepository,
        PaginatorInterface $paginator, // Inject Paginator
        Request $request // Inject Request
    ): Response {
        $queryBuilder = $ecoleRepository->createQueryBuilder('e');

        // Gestion de la recherche
        $search = $request->query->get('search');
        if ($search) {
            $queryBuilder->andWhere('e.nom LIKE :search OR e.ville LIKE :search OR e.codePostal LIKE :search OR e.code LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Le tri est géré par KnpPaginator via les paramètres de requête (sort, direction)
        // $queryBuilder->orderBy('e.nom', 'ASC'); // Le tri initial peut être défini ici ou via les options du paginateur

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Requête, PAS le résultat
            $request->query->getInt('page', 1), // Numéro de page
            10, // Limite par page
            ['defaultSortFieldName' => 'e.nom', 'defaultSortDirection' => 'asc'] // Options de tri par défaut
        );

        return $this->render('ecole/index.html.twig', [
            'ecoles' => $pagination, // Passer l'objet de pagination
            'current_search' => $search, // Pour pré-remplir le champ de recherche
        ]);
    }

    #[Route('/new', name: 'app_ecole_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ecole = new Ecole();
        $form = $this->createForm(EcoleForm::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ecole);
            $entityManager->flush();
            $this->addFlash('success', 'École créée avec succès.');
            return $this->redirectToRoute('app_ecole_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ecole/new.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ecole_show', methods: ['GET'])]
    public function show(Ecole $ecole, PriseDeVueRepository $priseDeVueRepository): Response // AJOUTER PriseDeVueRepository
    {
        $this->denyAccessUnlessGranted(EcoleVoter::VIEW, $ecole, "Vous n'avez pas accès à cette école.");

        // Récupérer les dernières prises de vue pour cette école (par exemple, les 5 dernières)
        $dernieresPrisesDeVue = $priseDeVueRepository->findBy(
            ['ecole' => $ecole],
            ['date' => 'DESC'], // Trier par date décroissante
            5 // Limiter aux 5 plus récentes
        );

        return $this->render('ecole/show.html.twig', [
            'ecole' => $ecole,
            'dernieres_prises_de_vue' => $dernieresPrisesDeVue, // PASSER LES PRISES DE VUE AU TEMPLATE
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ecole_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ecole $ecole, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EcoleForm::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'École modifiée avec succès.');
            return $this->redirectToRoute('app_ecole_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ecole_delete', methods: ['POST'])]
    public function delete(Request $request, Ecole $ecole, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ecole->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ecole);
            $entityManager->flush();
            $this->addFlash('success', 'École supprimée avec succès.');
        }

        return $this->redirectToRoute('app_ecole_index', [], Response::HTTP_SEE_OTHER);
    }
}
