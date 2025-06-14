<?php

namespace App\Controller;

use Psr\Log\LoggerInterface; // Importer LoggerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private LoggerInterface $logger; // Ajouter la propriété logger

    public function __construct(LoggerInterface $logger) // Injecter le logger
    {
        $this->logger = $logger;
    }

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $this->logger->info('[SecurityController] Accès à la page de connexion.');

        // if ($this->getUser()) {
        //     $this->logger->info('[SecurityController] Utilisateur déjà connecté, redirection.');
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->logger->error('[SecurityController] Erreur d\'authentification : {error_message} pour l\'utilisateur : {username}', [
                'error_message' => $error->getMessage(),
                'username' => $lastUsername
            ]);
        }
        if ($lastUsername) {
            $this->logger->info('[SecurityController] Dernière tentative de connexion pour : {username}', ['username' => $lastUsername]);
        }


        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code n'est jamais exécuté car la route est interceptée par le pare-feu
        $this->logger->info('[SecurityController] Tentative de déconnexion (interceptée par le pare-feu).');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}