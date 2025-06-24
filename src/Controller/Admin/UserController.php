<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserManager;

#[Route('/admin/users', name: 'admin_user_')]
#[IsGranted('ROLE_SUPERADMIN')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $data = $this->userManager->findAllPaginated($page, 10);
        
        return $this->render('admin/user/index.html.twig', [
            'users' => $data['results'],
            'totalItems' => $data['totalItems'],
            'currentPage' => $page,
            'itemsPerPage' => 10,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = $this->userManager->createNew();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            
            $result = $this->userManager->save($user, $plainPassword);

            if ($result['success']) {
                $this->addFlash('success', 'L\'utilisateur a été créé avec succès.');
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'canDelete' => $this->userManager->canDelete($user),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $currentUser = $this->getUser();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();
            $plainPassword = $form->get('plainPassword')->getData();

            $newRoles = $selectedRole ? [$selectedRole] : ['ROLE_PHOTOGRAPHE'];

            $roleResult = $this->userManager->updateRoles($user, $newRoles, $currentUser);
            if (!$roleResult['success']) {
                foreach ($roleResult['errors'] as $error) {
                    $this->addFlash('danger', $error);
                }
                return $this->redirectToRoute('admin_user_index');
            }

            $result = $this->userManager->save($user, $plainPassword);
            
            if ($result['success']) {
                $this->addFlash('success', 'L\'utilisateur a été modifié avec succès.');
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $result = $this->userManager->delete($user);
            
            if ($result['success']) {
                $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('danger', $error);
                }
            }
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
