<?php

namespace App\Controller\Admin;

use App\Entity\Ecole;
use App\Entity\PriseDeVue;
use App\Form\PriseDeVueType;
use App\Repository\EcoleRepository;
use App\Repository\PriseDeVueRepository;
use App\Service\PriseDeVueManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/prise-de-vue')]
class PriseDeVueController extends AbstractController
{
    private PriseDeVueManager $priseDeVueManager;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        PriseDeVueManager $priseDeVueManager,
        EntityManagerInterface $entityManager
    ) {
        $this->priseDeVueManager = $priseDeVueManager;
        $this->entityManager = $entityManager;
    }
    
    #[Route('/', name: 'admin_prise_de_vue_index', methods: ['GET'])]
    public function index(Request $request, PriseDeVueRepository $priseDeVueRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        // Si c'est un photographe, ne montrer que ses prises de vue
        $criteria = [];
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['photographe'] = $this->getUser();
        }
        
        // Filtrer par école si spécifié
        if ($request->query->has('ecole') && $request->query->get('ecole')) {
            $criteria['ecole'] = $request->query->get('ecole');
        }
        
        // Filtrer par date si spécifié
        if ($request->query->has('dateStart') && $request->query->get('dateStart')) {
            $criteria['dateDebut'] = new \DateTime($request->query->get('dateStart'));
        }
        
        if ($request->query->has('dateEnd') && $request->query->get('dateEnd')) {
            $criteria['dateFin'] = new \DateTime($request->query->get('dateEnd'));
        }
        
        $data = $priseDeVueRepository->search($criteria, $page, $limit);
        
        return $this->render('admin/prise_de_vue/index.html.twig', [
            'prises_de_vue' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => $limit,
            'ecoles' => $this->entityManager->getRepository(Ecole::class)->findAllOrderedByName(),
        ]);
    }
    
    #[Route('/new', name: 'admin_prise_de_vue_new', methods: ['GET', 'POST'])]
    #[IsGranted(\App\Security\Voter\PriseDeVueVoter::PRISEDEVUE_CREATE)]
    public function new(
        Request $request, 
        Ecole $ecole = null
    ): Response {
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
    
    #[Route('/{id}', name: 'admin_prise_de_vue_show', methods: ['GET'])]
    public function show(PriseDeVue $priseDeVue): Response
    {
        // Vérifier les droits d'accès avec le Voter
        $this->denyAccessUnlessGranted(\App\Security\Voter\PriseDeVueVoter::PRISEDEVUE_VIEW, $priseDeVue);
        
        // Calculer les prix totaux
        $prixTotaux = $this->priseDeVueManager->calculerPrixTotal($priseDeVue);
        
        return $this->render('admin/prise_de_vue/show.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'prix_totaux' => $prixTotaux,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'admin_prise_de_vue_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PriseDeVue $priseDeVue): Response
    {
        // Vérifier les droits d'accès avec le Voter
        $this->denyAccessUnlessGranted(\App\Security\Voter\PriseDeVueVoter::PRISEDEVUE_EDIT, $priseDeVue);
        
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
            'is_photographe' => !$this->isGranted('ROLE_ADMIN'),
        ]);
    }
    
    #[Route('/{id}/delete', name: 'admin_prise_de_vue_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
        // Cloner la prise de vue
        $clone = $this->priseDeVueManager->cloner($priseDeVue);
        
        // Persister le clone
        $result = $this->priseDeVueManager->save($clone);
        
        if ($result['success']) {
            $this->addFlash('success', 'La prise de vue a été clonée avec succès.');
            return $this->redirectToRoute('admin_prise_de_vue_edit', ['id' => $clone->getId()]);
        } else {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('admin_prise_de_vue_show', ['id' => $priseDeVue->getId()]);
        }
    }
    
    #[Route('/ecole/{id}', name: 'admin_prise_de_vue_list_by_ecole', methods: ['GET'])]
    public function listByEcole(Request $request, Ecole $ecole, PriseDeVueRepository $repository): Response
    {
        // Vérifier les droits d'accès
        $this->denyAccessUnlessGranted('view', $ecole);
        
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        $criteria = [
            'ecole' => $ecole
        ];
        
        // Si c'est un photographe, ne montrer que ses prises de vue
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['photographe'] = $this->getUser();
        }
        
        $data = $repository->search($criteria, $page, $limit);
        
        return $this->render('admin/prise_de_vue/list_by_ecole.html.twig', [
            'ecole' => $ecole,
            'prises_de_vue' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => $limit,
        ]);
    }
}