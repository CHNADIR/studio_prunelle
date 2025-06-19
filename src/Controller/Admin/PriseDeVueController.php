<?php

namespace App\Controller\Admin;

use App\Entity\PriseDeVue;
use App\Entity\Ecole;
use App\Form\PriseDeVueType;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/admin/prise-de-vue')]
class PriseDeVueController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    // ... autres méthodes ...

    #[Route('/new', name: 'admin_prise_de_vue_new', methods: ['GET', 'POST'])]
    #[Route('/new/ecole/{ecole_id}', name: 'admin_prise_de_vue_new_with_ecole', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EcoleRepository $ecoleRepository): Response
    {
        $priseDeVue = new PriseDeVue();
        
        // Pré-sélectionner une école si l'ID est fourni
        $ecoleId = $request->get('ecole_id');
        if ($ecoleId) {
            $ecole = $ecoleRepository->find($ecoleId);
            if ($ecole) {
                $priseDeVue->setEcole($ecole);
            }
        }
        
        // Si c'est un photographe, pré-sélectionner l'utilisateur courant
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && !$this->security->isGranted('ROLE_ADMIN')) {
            $priseDeVue->setPhotographe($this->getUser());
        }
        
        $form = $this->createForm(PriseDeVueType::class, $priseDeVue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si c'est un photographe, s'assurer que le photographe est bien l'utilisateur courant
            if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && !$this->security->isGranted('ROLE_ADMIN')) {
                $priseDeVue->setPhotographe($this->getUser());
            }
            
            $entityManager->persist($priseDeVue);
            $entityManager->flush();

            $this->addFlash('success', 'La prise de vue a été créée avec succès.');
            return $this->redirectToRoute('admin_prise_de_vue_index');
        }

        return $this->render('admin/prise_de_vue/new.html.twig', [
            'prise_de_vue' => $priseDeVue,
            'form' => $form->createView(),
        ]);
    }
    
}