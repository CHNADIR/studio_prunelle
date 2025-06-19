<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Form\ThemeType;
use App\Repository\ThemeRepository;
use App\Service\ReferentialManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/theme')]
#[IsGranted('ROLE_ADMIN')]
class ThemeController extends AbstractReferentialController
{
    private ReferentialManager $referentialManager;

    public function __construct(ReferentialManager $referentialManager)
    {
        $this->referentialManager = $referentialManager;
    }

    #[Route('/', name: 'admin_theme_index', methods: ['GET'])]
    public function index(ThemeRepository $themeRepository): Response
    {
        return $this->render('admin/theme/index.html.twig', [
            'themes' => $themeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_theme_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $theme = new Theme();
        $theme->setActive(true);
        
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($theme);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le thème a été créé avec succès.');
                return $this->redirectToRoute('admin_theme_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/theme/new.html.twig', [
            'theme' => $theme,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modal-new', name: 'admin_theme_modal_new', methods: ['GET', 'POST'])]
    public function modalNew(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $theme = new Theme();
        $theme->setActive(true);
        
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        return $this->handleModalNew(
            $request,
            $entityManager,
            $validator,
            $theme,
            $form,
            'admin/theme/modal_new.html.twig',
            'Le thème a été créé avec succès.',
            'admin_theme_index'
        );
    }

    #[Route('/{id}', name: 'admin_theme_show', methods: ['GET'])]
    public function show(Theme $theme): Response
    {
        return $this->render('admin/theme/show.html.twig', [
            'theme' => $theme,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_theme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Theme $theme): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($theme);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le thème a été modifié avec succès.');
                return $this->redirectToRoute('admin_theme_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/theme/edit.html.twig', [
            'theme' => $theme,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_theme_delete', methods: ['POST'])]
    public function delete(Request $request, Theme $theme): Response
    {
        if ($this->isCsrfTokenValid('delete'.$theme->getId(), $request->request->get('_token'))) {
            // Vérifier si le thème peut être supprimé
            if (!$this->referentialManager->canDelete($theme, 'prisesDeVue')) {
                $this->addFlash('error', 'Ce thème ne peut pas être supprimé car il est utilisé par des prises de vue.');
                return $this->redirectToRoute('admin_theme_index');
            }
            
            $result = $this->referentialManager->delete($theme);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le thème a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        }

        return $this->redirectToRoute('admin_theme_index');
    }
}