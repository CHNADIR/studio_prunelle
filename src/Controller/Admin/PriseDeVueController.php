<?php

namespace App\Controller\Admin;

use App\Entity\PriseDeVue;
use App\Form\PriseDeVueType;
use App\Form\CommentaireType;
use App\Repository\EcoleRepository;
use App\Repository\PriseDeVueRepository;
use App\Security\Voter\PriseDeVueVoter;
use App\Service\PriseDeVueManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/prise-de-vue')]
class PriseDeVueController extends AbstractController
{
    private PriseDeVueManager $priseDeVueManager;
    
    public function __construct(PriseDeVueManager $priseDeVueManager)
    {
        $this->priseDeVueManager = $priseDeVueManager;
    }
    
    #[Route('/', name: 'admin_prise_de_vue_index', methods: ['GET'])]
    #[IsGranted('ROLE_PHOTOGRAPHE')]
    public function index(
        Request $request, 
        PriseDeVueRepository $priseDeVueRepository,
        EcoleRepository $ecoleRepository
    ): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = $request->query->getInt('limit', 10);
        
        $criteria = [];
        
        if ($request->query->has('ecole') && $request->query->get('ecole')) {
            $criteria['ecole'] = $request->query->get('ecole');
        }
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['photographe'] = $this->getUser();
        }
        
        if ($request->query->has('dateStart') && $request->query->get('dateStart')) {
            $criteria['dateDebut'] = new \DateTime($request->query->get('dateStart'));
        }
        
        if ($request->query->has('dateEnd') && $request->query->get('dateEnd')) {
            $criteria['dateFin'] = new \DateTime($request->query->get('dateEnd'));
        }
        
        $data = $this->priseDeVueManager->findByCriteriaPaginated($criteria, $page, $limit);
        
        $ecoles = $ecoleRepository->findAllOrderedByName();
        
        return $this->render('admin/prise_de_vue/index.html.twig', [
            'prises_de_vue' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => $limit,
            'criteria' => $criteria,
            'ecoles' => $ecoles
        ]);
    }
    
    #[Route('/new', name: 'admin_prise_de_vue_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHE')]
    public function new(Request $request, EcoleRepository $ecoleRepository): Response
    {
        $ecole = null;
        if ($request->query->has('ecole')) {
            $ecoleId = $request->query->get('ecole');
            $ecole = $ecoleRepository->find($ecoleId);
        }
        
        $priseDeVue = new PriseDeVue();
        $priseDeVue->setDatePdv(new \DateTime());
        
        if ($ecole) {
            $priseDeVue->setEcole($ecole);
        }
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $priseDeVue->setPhotographe($this->getUser());
        }
        
        $form = $this->createForm(PriseDeVueType::class, $priseDeVue);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $priseDeVue->setPhotographe($this->getUser());
            }
            
            $result = $this->priseDeVueManager->save($priseDeVue);
            
            if ($result['success']) {
                $this->addFlash('success', 'La prise de vue a été créée avec succès.');
                return $this->redirectToRoute('admin_prise_de_vue_show', ['id' => $priseDeVue->getId()]);
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('admin/prise_de_vue/new.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'admin_prise_de_vue_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(PriseDeVueVoter::PRISEDEVUE_VIEW, subject: 'priseDeVue')]
    public function show(PriseDeVue $priseDeVue): Response
    {
        $prixTotaux = $this->priseDeVueManager->calculerPrixTotal($priseDeVue);
        
        $canComment = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_COMMENT, $priseDeVue);
        
        $canEdit = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_EDIT, $priseDeVue);
        
        $canDelete = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_DELETE, $priseDeVue);
        
        return $this->render('admin/prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'prix_totaux' => $prixTotaux,
            'can_comment' => $canComment,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);
    }
    
    #[Route('/{id}/comment', name: 'admin_prise_de_vue_update_comment', methods: ['POST'])]
    #[IsGranted(PriseDeVueVoter::PRISEDEVUE_COMMENT, subject: 'priseDeVue')]
    public function updateComment(Request $request, PriseDeVue $priseDeVue): Response
    {
        $commentaire = $request->request->get('commentaire');
        
        $result = $this->priseDeVueManager->updateComment($priseDeVue, $commentaire);
        
        if ($request->isXmlHttpRequest()) {
            if ($result['success']) {
                return $this->json([
                    'success' => true,
                    'message' => 'Commentaire mis à jour avec succès',
                    'commentaire' => $priseDeVue->getCommentaire()
                ]);
            }
            
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du commentaire'
            ], 400);
        }
        
        if ($result['success']) {
            $this->addFlash('success', 'Commentaire mis à jour avec succès');
        } else {
            $this->addFlash('error', 'Erreur lors de la mise à jour du commentaire');
        }
        
        return $this->redirectToRoute('admin_prise_de_vue_show', ['id' => $priseDeVue->getId()]);
    }
    
    #[Route('/{id}/edit', name: 'admin_prise_de_vue_edit', methods: ['GET', 'POST'])]
    #[IsGranted(PriseDeVueVoter::PRISEDEVUE_EDIT, subject: 'priseDeVue')]
    public function edit(Request $request, PriseDeVue $priseDeVue): Response
    {
        $form = $this->createForm(PriseDeVueType::class, $priseDeVue);
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $form = $this->createFormBuilder($priseDeVue)
                ->add('commentaire', TextareaType::class, [
                    'label' => 'Commentaires',
                    'required' => false,
                    'attr' => [
                        'rows' => 4,
                        'class' => 'form-control'
                    ],
                ])
                ->getForm();
        }
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->priseDeVueManager->save($priseDeVue);
            
            if ($result['success']) {
                $this->addFlash('success', 'La prise de vue a été modifiée avec succès.');
                return $this->redirectToRoute('admin_prise_de_vue_show', ['id' => $priseDeVue->getId()]);
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }
        
        return $this->render('admin/prise_de_vue/edit.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'form' => $form->createView(),
            'is_photographe' => !$this->isGranted('ROLE_ADMIN')
        ]);
    }
    
    #[Route('/{id}/delete', name: 'admin_prise_de_vue_delete', methods: ['POST'])]
    #[IsGranted(PriseDeVueVoter::PRISEDEVUE_DELETE, subject: 'priseDeVue')]
    public function delete(Request $request, PriseDeVue $priseDeVue): Response
    {
        if ($this->isCsrfTokenValid('delete'.$priseDeVue->getId(), $request->request->get('_token'))) {
            $this->priseDeVueManager->delete($priseDeVue);
            $this->addFlash('success', 'La prise de vue a été supprimée avec succès.');
        }
        
        return $this->redirectToRoute('admin_prise_de_vue_index');
    }
    
    #[Route('/{id}/clone', name: 'admin_prise_de_vue_clone', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function clone(PriseDeVue $priseDeVue): Response
    {
        $clonedPriseDeVue = $this->priseDeVueManager->clonePriseDeVue($priseDeVue);
        
        $this->addFlash('success', 'La prise de vue a été clonée avec succès.');
        
        return $this->redirectToRoute('admin_prise_de_vue_edit', ['id' => $clonedPriseDeVue->getId()]);
    }
    
    #[Route('/test-interface', name: 'admin_prise_de_vue_test_interface', methods: ['GET'])]
    public function testInterface(): Response
    {
        return $this->render('admin/prise_de_vue/test_interface.html.twig');
    }
}