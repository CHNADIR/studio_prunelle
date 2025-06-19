<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EcoleVoter extends Voter
{
    // Actions possibles
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    
    private Security $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifier si l'attribut est supporté
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }
        
        // Vérifier si le sujet est une instance d'Ecole
        if (!$subject instanceof Ecole) {
            return false;
        }
        
        return true;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // Si l'utilisateur n'est pas connecté, refuser
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // Les administrateurs peuvent tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // Un photographe peut voir une école mais pas la modifier ou la supprimer
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && $attribute === self::VIEW) {
            return true;
        }
        
        return false;
    }
}