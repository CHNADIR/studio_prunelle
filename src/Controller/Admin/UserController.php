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
use App\Service\UserSecurityHelper;

#[Route('/admin/users', name: 'admin_user_')]
#[IsGranted('ROLE_SUPERADMIN')]
class UserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $superAdmins = array_filter($users, fn($u) => in_array('ROLE_SUPERADMIN', $u->getRoles()));
        $isLastSuperAdmin = count($superAdmins) === 1;

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'isLastSuperAdmin' => $isLastSuperAdmin,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserManager $userManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $userManager->save($user, $plainPassword);

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserManager $userManager, UserSecurityHelper $userSecurityHelper): Response
    {
        $currentUser = $this->getUser();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newRoles = $form->get('roles')->getData();

            // Protection : empêcher de retirer son propre rôle superadmin si c'est le dernier
            if ($user === $currentUser
                && $userSecurityHelper->isLastSuperAdmin($user)
                && !in_array('ROLE_SUPERADMIN', $newRoles)
            ) {
                $this->addFlash('danger', 'Impossible de retirer le dernier rôle SuperAdmin.');
                return $this->redirectToRoute('admin_user_index');
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $userManager->save($user, $plainPassword);

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, UserSecurityHelper $userSecurityHelper): Response
    {
        $currentUser = $this->getUser();

        // Protection : empêcher la suppression de soi-même si dernier SuperAdmin
        if ($user === $currentUser && $userSecurityHelper->isLastSuperAdmin($currentUser)) {
            $this->addFlash('danger', 'Impossible de supprimer le dernier SuperAdmin.');
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
