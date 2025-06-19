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
        
        // Filtrer par école si spécifié
        if ($request->query->has('ecole') && $request->query->get('ecole')) {
            $criteria['ecole'] = $request->query->get('ecole');
        }
        
        // Si c'est un photographe (non admin), on filtre sur son id
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['photographe'] = $this->getUser();
        }
        
        // Filtrer par date si spécifié
        if ($request->query->has('dateStart') && $request->query->get('dateStart')) {
            $criteria['dateDebut'] = new \DateTime($request->query->get('dateStart'));
        }
        
        if ($request->query->has('dateEnd') && $request->query->get('dateEnd')) {
            $criteria['dateFin'] = new \DateTime($request->query->get('dateEnd'));
        }
        
        $data = $this->priseDeVueManager->findByCriteriaPaginated($criteria, $page, $limit);
        
        // Récupérer la liste des écoles pour le filtre
        $ecoles = $ecoleRepository->findAllOrderedByName();
        
        return $this->render('admin/prise_de_vue/index.html.twig', [
            'prises_de_vue' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => $limit,
            'criteria' => $criteria,
            'ecoles' => $ecoles // Ajout de la liste des écoles
        ]);
    }
    
    #[Route('/new', name: 'admin_prise_de_vue_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PHOTOGRAPHE')]
    public function new(Request $request, EcoleRepository $ecoleRepository): Response
    {
        // Si un ID d'école est fourni, charger l'école
        $ecole = null;
        if ($request->query->has('ecole')) {
            $ecoleId = $request->query->get('ecole');
            $ecole = $ecoleRepository->find($ecoleId);
        }
        
        // Créer une nouvelle instance vide
        $priseDeVue = new PriseDeVue();
        $priseDeVue->setDate(new \DateTime());
        
        // Si une école est fournie, l'associer
        if ($ecole) {
            $priseDeVue->setEcole($ecole);
        }
        
        // Si c'est un photographe, pré-sélectionner l'utilisateur courant
        if (!$this->isGranted('ROLE_ADMIN')) {
            $priseDeVue->setPhotographe($this->getUser());
        }
        
        // Créer le formulaire
        $form = $this->createForm(PriseDeVueType::class, $priseDeVue);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Assurer que si c'est un photographe, il ne peut pas modifier le photographe
            if (!$this->isGranted('ROLE_ADMIN')) {
                $priseDeVue->setPhotographe($this->getUser());
            }
            
            // Persister l'entité
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
        // Calculer les prix totaux
        $prixTotaux = $this->priseDeVueManager->calculerPrixTotal($priseDeVue);
        
        // Vérifier si l'utilisateur peut commenter
        $canComment = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_COMMENT, $priseDeVue);
        
        // Vérifier si l'utilisateur peut modifier entièrement
        $canEdit = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_EDIT, $priseDeVue);
        
        // Vérifier si l'utilisateur peut supprimer
        $canDelete = $this->isGranted(PriseDeVueVoter::PRISEDEVUE_DELETE, $priseDeVue);
        
        // Rendu de la vue
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
        // Récupérer le commentaire depuis la requête
        $commentaire = $request->request->get('commentaire');
        
        // Mettre à jour le commentaire via le service
        $result = $this->priseDeVueManager->updateComment($priseDeVue, $commentaire);
        
        // Si c'est une requête AJAX
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
        
        // Si ce n'est pas une requête AJAX
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
        // Créer le formulaire
        $form = $this->createForm(PriseDeVueType::class, $priseDeVue);
        
        // Si c'est un photographe, il ne peut modifier que le commentaire
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
            // Persister les modifications
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
}