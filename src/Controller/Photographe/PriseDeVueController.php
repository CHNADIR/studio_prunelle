<?php

namespace App\Controller\Photographe;

use App\Entity\PriseDeVue;
use App\Form\CommentaireType;
use App\Repository\PriseDeVueRepository;
use App\Security\Attribute\EntityAction;
use App\Service\PriseDeVueManager;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/photographe/prise-de-vue')]
#[IsGranted('ROLE_PHOTOGRAPHE')]
class PriseDeVueController extends AbstractController
{
    public function __construct(
        private readonly PriseDeVueManager     $priseDeVueManager,
        private readonly PriseDeVueRepository  $priseDeVueRepository,
    ) {}

    #[Route('/', name: 'photographe_prise_de_vue_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $qb = $this->priseDeVueRepository->qbMesPrises($this->getUser());
        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage(10)
              ->setCurrentPage($request->query->getInt('page', 1));

        return $this->render('photographe/prise_de_vue/index.html.twig', [
            'pager' => $pager,
        ]);
    }

    #[Route('/{id}', name: 'photographe_prise_de_vue_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(Request $request, PriseDeVue $priseDeVue): Response
    {
        $this->denyAccessUnlessGranted(EntityAction::PRISEDEVUE_VIEW->value, $priseDeVue);

        $commentForm = null;
        if ($this->isGranted(EntityAction::PRISEDEVUE_COMMENT->value, $priseDeVue)) {
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

        $prixTotaux = $this->priseDeVueManager->calculerPrixTotal($priseDeVue);

        return $this->render('photographe/prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'prix_totaux'  => $prixTotaux,
            'comment_form' => $commentForm?->createView(),
        ]);
    }
}
