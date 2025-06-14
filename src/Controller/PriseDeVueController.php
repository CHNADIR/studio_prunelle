<?php

namespace App\Controller;

use App\Entity\PriseDeVue;
use App\Form\PriseDeVueForm;
use App\Repository\PriseDeVueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; // Import PaginatorInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Import Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Security\Voter\PriseDeVueVoter; // Ajoutez cette ligne
use Symfony\Component\Security\Http\Attribute\IsGranted; // Pour l'attribut
use Symfony\Component\Security\Core\Security; // Importer Security
use App\Entity\User; // Assurez-vous d'importer votre entité User

#[Route('/prise/de/vue')]
final class PriseDeVueController extends AbstractController
{
    #[Route(name: 'app_prise_de_vue_index', methods: ['GET'])]
    public function index(
        PriseDeVueRepository $priseDeVueRepository,
        PaginatorInterface $paginator,
        Request $request,
        Security $security // Inject Security
    ): Response {
        $queryBuilder = $priseDeVueRepository->createQueryBuilder('pdv')
            ->leftJoin('pdv.ecole', 'e') // Jointure avec Ecole
            ->leftJoin('pdv.theme', 'th') // Jointure avec Theme
            ->addSelect('e') // Sélectionner les données de l'école pour éviter des requêtes N+1
            ->addSelect('th'); // Sélectionner les données du thème

        // Si l'utilisateur n'est pas admin, filtrer par photographe (email de l'utilisateur)
        if (!$security->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if ($user instanceof User) { // Assurez-vous que c'est votre entité User
                $queryBuilder->andWhere('pdv.photographe = :userEmail')
                    ->setParameter('userEmail', $user->getEmail());
            } else {
                // Si pas d'utilisateur ou type incorrect, ne rien afficher pour éviter fuite de données
                $queryBuilder->andWhere('1 = 0');
            }
        }

        $search = $request->query->get('search');
        if ($search) {
            $queryBuilder
                ->andWhere('pdv.photographe LIKE :search OR e.nom LIKE :search OR th.nom LIKE :search OR DATE_FORMAT(pdv.date, \'%Y-%m-%d\') LIKE :search_date_format')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('search_date_format', '%' . $search . '%'); // Pour rechercher sur la date formatée
        }
        
        // Définir les alias pour le tri sur les champs des entités jointes
        $sortFieldWhitelist = ['pdv.id', 'pdv.date', 'pdv.photographe', 'e.nom', 'th.nom', 'pdv.nombreEleves'];
        $sort = $request->query->get('sort', 'pdv.date'); // Champ de tri par défaut
        $direction = $request->query->get('direction', 'desc'); // Direction par défaut

        if (!in_array($sort, $sortFieldWhitelist)) {
            $sort = 'pdv.date'; // Sécurité pour éviter le tri sur des champs non autorisés
        }
        
        // KnpPaginator gère le tri via les paramètres de requête, mais nous devons nous assurer que les alias sont corrects
        // $queryBuilder->orderBy($sort, $direction); // KnpPaginator s'en charge

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10, // Limite par page
            [
                'defaultSortFieldName' => $sort, // Utiliser le champ de tri actuel ou par défaut
                'defaultSortDirection' => $direction,
                'sortFieldWhitelist' => $sortFieldWhitelist, // Important pour la sécurité
            ]
        );

        return $this->render('prise_de_vue/index.html.twig', [
            'prise_de_vues' => $pagination,
            'current_search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_prise_de_vue_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priseDeVue = new PriseDeVue();
        $form = $this->createForm(PriseDeVueForm::class, $priseDeVue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($priseDeVue);
            $entityManager->flush();
            $this->addFlash('success', 'Prise de vue créée avec succès.');
            return $this->redirectToRoute('app_prise_de_vue_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prise_de_vue/new.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prise_de_vue_show', methods: ['GET'])]
    // #[IsGranted(PriseDeVueVoter::VIEW, subject: 'priseDeVue')] // Utilisation de l'attribut
    public function show(PriseDeVue $priseDeVue): Response
    {
        // Ou vérification manuelle :
        $this->denyAccessUnlessGranted(PriseDeVueVoter::VIEW, $priseDeVue, "Vous n'avez pas accès à cette prise de vue.");

        return $this->render('prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prise_de_vue_edit', methods: ['GET', 'POST'])]
    // #[IsGranted(PriseDeVueVoter::EDIT, subject: 'priseDeVue')]
    public function edit(Request $request, PriseDeVue $priseDeVue, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PriseDeVueVoter::EDIT, $priseDeVue, "Vous n'êtes pas autorisé à modifier cette prise de vue.");

        $form = $this->createForm(PriseDeVueForm::class, $priseDeVue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Prise de vue modifiée avec succès.');
            return $this->redirectToRoute('app_prise_de_vue_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prise_de_vue/edit.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prise_de_vue_delete', methods: ['POST'])]
    public function delete(Request $request, PriseDeVue $priseDeVue, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(PriseDeVueVoter::DELETE, $priseDeVue, "Vous n'êtes pas autorisé à supprimer cette prise de vue.");

        if ($this->isCsrfTokenValid('delete'.$priseDeVue->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($priseDeVue);
            $entityManager->flush();
            $this->addFlash('success', 'Prise de vue supprimée avec succès.');
        }

        return $this->redirectToRoute('app_prise_de_vue_index', [], Response::HTTP_SEE_OTHER);
    }
}
