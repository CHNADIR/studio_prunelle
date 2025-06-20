<?php

namespace App\Controller\Photographe;

use App\Entity\PriseDeVue;
use App\Form\CommentaireType;
use App\Repository\PriseDeVueRepository;
use App\Security\Voter\PriseDeVueVoter;
use App\Service\PriseDeVueManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace photographe – liste et détail de SES prises de vue
 *
 * Sécurité :
 *   • Accès global : ROLE_PHOTOGRAPHE
 *   • Granularité : PriseDeVueVoter (VIEW / COMMENT)
 */
#[Route('/photographe/prise-de-vue')]
#[IsGranted('ROLE_PHOTOGRAPHE')]
class PriseDeVueController extends AbstractController
{
    public function __construct(
        private readonly PriseDeVueManager     $priseDeVueManager,
        private readonly PriseDeVueRepository  $priseDeVueRepository,
    ) {}

    // ──────────────────────────────────────────────────────────
    // LISTE PAGINÉE
    // ──────────────────────────────────────────────────────────
    #[Route('/', name: 'photographe_prise_de_vue_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // On suppose que tu as une méthode qbMesPrises($user) dans PriseDeVueRepository
        $qb = $this->priseDeVueRepository->qbMesPrises($this->getUser());
        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage(10)
              ->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('photographe/prise_de_vue/index.html.twig', [
            'pager' => $pager,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // DÉTAIL + COMMENTAIRE
    // ──────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'photographe_prise_de_vue_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(Request $request, PriseDeVue $priseDeVue): Response
    {
        // Vue autorisée seulement si le voter confirme la propriété
        $this->denyAccessUnlessGranted(PriseDeVueVoter::PRISEDEVUE_VIEW, $priseDeVue);

        /* -------- Formulaire de commentaire (autorisé via voter) -------- */
        $commentForm = null;
        if ($this->isGranted(PriseDeVueVoter::PRISEDEVUE_COMMENT, $priseDeVue)) {
            $commentForm = $this->createForm(CommentaireType::class, $priseDeVue);
            $commentForm->handleRequest($request);

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $this->priseDeVueManager->save($priseDeVue);
                $this->addFlash('success', 'Commentaire mis à jour.');
                return $this->redirectToRoute('photographe_prise_de_vue_show', [
                    'id' => $priseDeVue->getId(),
                ]);
            }
        }

        /* -------- Infos complémentaires -------- */
        $prixTotaux = $this->priseDeVueManager->calculerPrixTotal($priseDeVue);

        return $this->render('photographe/prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'prix_totaux'  => $prixTotaux,
            'comment_form' => $commentForm?->createView(),
        ]);
    }
}
