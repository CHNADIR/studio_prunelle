<?php

namespace App\Controller;

use App\Entity\PriseDeVue;
use App\Form\PriseDeVueForm;
use App\Repository\PriseDeVueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Security\Voter\PriseDeVueVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security as CoreSecurity; // Renommé pour éviter conflit
use App\Entity\User;

#[Route('/prise/de/vue')]
final class PriseDeVueController extends AbstractController
{
    #[Route(name: 'app_prise_de_vue_index', methods: ['GET'])]
    public function index(
        PriseDeVueRepository $priseDeVueRepository,
        PaginatorInterface $paginator,
        Request $request,
        CoreSecurity $security // Utilisation de l'alias
    ): Response {
        $queryBuilder = $priseDeVueRepository->createQueryBuilder('pdv')
            ->leftJoin('pdv.ecole', 'e')
            ->leftJoin('pdv.theme', 'th')
            ->addSelect('e')
            ->addSelect('th');

        $currentUser = $this->getUser();
        // Filtrer les prises de vue pour les photographes non-admin/non-responsable
        if ($currentUser instanceof User && $security->isGranted('ROLE_PHOTOGRAPHE') && !$security->isGranted('ROLE_RESPONSABLE_ADMINISTRATIF') && !$security->isGranted('ROLE_ADMIN')) {
            $queryBuilder->andWhere('pdv.photographe = :photographeEmail')
                         ->setParameter('photographeEmail', $currentUser->getEmail());
        }
        // Les admins et responsables administratifs voient tout par défaut, pas besoin de filtre supplémentaire ici
        // sauf si une logique plus spécifique est nécessaire.
        // Si un utilisateur n'a aucun des rôles ci-dessus mais accède (ce qui ne devrait pas arriver avec access_control),
        // vous pourriez vouloir retourner une liste vide ou une erreur 403.
        // Cependant, votre access_control devrait déjà gérer cela.

        $search = $request->query->get('search');
        if ($search) {
            // Assurez-vous que les champs de recherche correspondent à votre logique métier
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('LOWER(pdv.photographe)', ':search_lower'),
                    $queryBuilder->expr()->like('LOWER(e.nom)', ':search_lower'),
                    $queryBuilder->expr()->like('LOWER(th.nom)', ':search_lower'),
                    $queryBuilder->expr()->like('DATE_FORMAT(pdv.date, \'%Y-%m-%d\')', ':search') // Recherche de date exacte
                )
            )
            ->setParameter('search_lower', '%' . strtolower($search) . '%')
            ->setParameter('search', '%' . $search . '%');
        }
        
        // KnpPaginatorBundle gère le tri basé sur les paramètres de la requête.
        // Les liens knp_pagination_sortable dans Twig génèrent ces paramètres.
        $defaultSortFieldName = 'pdv.date';
        $defaultSortDirection = 'desc';
        $itemsPerPage = 10; // Ou une valeur configurable

        // La liste blanche des champs de tri est importante pour la sécurité et la performance.
        $sortFieldWhitelist = ['pdv.id', 'pdv.date', 'pdv.photographe', 'e.nom', 'th.nom', 'pdv.nombreEleves'];

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Cible de la pagination
            $request->query->getInt('page', 1), // Numéro de page demandé
            $itemsPerPage, // Nombre d'éléments par page
            [
                'defaultSortFieldName' => $request->query->get('sort', $defaultSortFieldName),
                'defaultSortDirection' => $request->query->get('direction', $defaultSortDirection),
                'sortFieldWhitelist' => $sortFieldWhitelist, // Appliquer la liste blanche
            ]
        );

        // C'est ici que la variable 'prises_de_vue' est définie pour le template.
        $viewParameters = [
            'prises_de_vue' => $pagination,
            'current_search' => $search,
            // Ajoutez d'autres variables si nécessaire pour votre template
        ];

        return $this->render('prise_de_vue/index.html.twig', $viewParameters);
    }

    #[Route('/new', name: 'app_prise_de_vue_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_RESPONSABLE_ADMINISTRATIF')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priseDeVue = new PriseDeVue();
        $form = $this->createForm(PriseDeVueForm::class, $priseDeVue, ['can_edit_full' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($priseDeVue);
            $entityManager->flush();
            $this->addFlash('success', 'Prise de vue créée avec succès.');

            return $this->redirectToRoute('app_prise_de_vue_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prise_de_vue/new.html.twig', [
            'prise_de_vue' => $priseDeVue, // Nécessaire si _form.html.twig y fait référence directement
            'form' => $form->createView(), // Toujours passer la vue du formulaire
        ]);
    }

    #[Route('/{id}', name: 'app_prise_de_vue_show', methods: ['GET'])]
    public function show(PriseDeVue $priseDeVue): Response
    {
        $this->denyAccessUnlessGranted(PriseDeVueVoter::VIEW, $priseDeVue, "Vous n'avez pas accès à cette prise de vue.");
        
        return $this->render('prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prise_de_vue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PriseDeVue $priseDeVue, EntityManagerInterface $entityManager): Response
    {
        $canEditFull = $this->isGranted(PriseDeVueVoter::EDIT, $priseDeVue);
        $canEditComment = $this->isGranted(PriseDeVueVoter::EDIT_COMMENT, $priseDeVue);

        if (!$canEditFull && !$canEditComment) {
            $this->denyAccessUnlessGranted(PriseDeVueVoter::EDIT_COMMENT, $priseDeVue, "Vous n'êtes pas autorisé à modifier cette prise de vue.");
        }

        $form = $this->createForm(PriseDeVueForm::class, $priseDeVue, [
            'can_edit_full' => $canEditFull
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Prise de vue modifiée avec succès.');
            return $this->redirectToRoute('app_prise_de_vue_show', ['id' => $priseDeVue->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prise_de_vue/edit.html.twig', [
            'prise_de_vue' => $priseDeVue, // Nécessaire pour _delete_form.html.twig et le titre
            'form' => $form->createView(), // Toujours passer la vue du formulaire
            'can_edit_full' => $canEditFull // Si le template a besoin de cette info
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
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_prise_de_vue_index', [], Response::HTTP_SEE_OTHER);
    }
}
