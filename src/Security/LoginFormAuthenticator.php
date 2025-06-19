<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Optionally store last entered email in session
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

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): RedirectResponse
    {
        // Vérifier si l'utilisateur essayait d'accéder à une page protégée
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        
        // Sinon, rediriger en fonction du rôle
        $user = $token->getUser();
        if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
            // Rediriger vers la page d'accueil en attendant une page spécifique
            return new RedirectResponse($this->urlGenerator->generate('home'));
        } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Décommenter cette ligne pour rediriger vers la liste des écoles
            return new RedirectResponse($this->urlGenerator->generate('admin_ecole_index'));
        } elseif (in_array('ROLE_PHOTOGRAPHE', $user->getRoles())) {
            // Rediriger vers la page d'accueil en attendant une page spécifique
            return new RedirectResponse($this->urlGenerator->generate('home'));
        }
        
        // Par défaut, redirection vers la page d'accueil
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }
}