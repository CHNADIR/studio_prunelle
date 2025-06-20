<?php

namespace App\Security;

use App\Entity\User;
use App\Security\LoginThrottling\LoginThrottler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private LoginThrottler $loginThrottler;
    private Security $security; // Ajout du service Security

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager,
        LoginThrottler $loginThrottler,
        Security $security // Injection du service Security
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->loginThrottler = $loginThrottler;
        $this->security = $security;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        // Vérifier si l'utilisateur est bloqué
        if ($this->loginThrottler->isBlocked($email)) {
            throw new CustomUserMessageAuthenticationException(
                'Trop de tentatives de connexion. Veuillez réessayer dans une minute.'
            );
        }

        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Enregistrer cette tentative
        $this->loginThrottler->registerAttempt($email);

        // Stocker l'email pour la prochaine tentative
        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        // Mettre à jour la date de dernière connexion
        $user = $token->getUser();
        if ($user instanceof User) {
            $user->updateLastLogin();
            $this->entityManager->flush();
        }

        // Rediriger vers la page cible si elle existe
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Redirection basée sur le rôle - Utilisation du service Security injecté
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_prise_de_vue_index'));
        } elseif ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return new RedirectResponse($this->urlGenerator->generate('home'));
        }

        // Fallback vers la page d'accueil
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }
}