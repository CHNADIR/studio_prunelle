<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        
        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {
        // Méthode interceptée par Symfony Security
        throw new \LogicException('This method should not be reached');
    }

    #[Route('/logout', methods: ['GET'])]
    public function logoutGet(): Response
    {
        // Rediriger vers la page d'accueil avec un message
        $this->addFlash('warning', 'Pour vous déconnecter, veuillez utiliser le bouton de déconnexion.');
        return $this->redirectToRoute('home');
    }
}