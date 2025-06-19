<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PriseDeVueVoter extends Voter
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
        
        // Vérifier si le sujet est une instance de PriseDeVue
        if (!$subject instanceof PriseDeVue) {
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
        
        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;
        
        // Vérifier le droit spécifique
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($priseDeVue, $user);
            case self::EDIT:
                return $this->canEdit($priseDeVue, $user);
            case self::DELETE:
                return $this->canDelete($priseDeVue, $user);
        }
        
        return false;
    }
    
    /**
     * Détermine si l'utilisateur peut voir une prise de vue
     */
    private function canView(PriseDeVue $priseDeVue, UserInterface $user): bool
    {
        // Un photographe ne peut voir que ses prises de vue
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && !$this->security->isGranted('ROLE_ADMIN')) {
            return $priseDeVue->getPhotographe() && $priseDeVue->getPhotographe()->getId() === $user->getId();
        }
        
        return false;
    }
    
    /**
     * Détermine si l'utilisateur peut modifier une prise de vue
     */
    private function canEdit(PriseDeVue $priseDeVue, UserInterface $user): bool
    {
        // Un photographe ne peut modifier que ses prises de vue et seulement le commentaire
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && !$this->security->isGranted('ROLE_ADMIN')) {
            return $priseDeVue->getPhotographe() && $priseDeVue->getPhotographe()->getId() === $user->getId();
        }
        
        return false;
    }
    
    /**
     * Détermine si l'utilisateur peut supprimer une prise de vue
     */
    private function canDelete(PriseDeVue $priseDeVue, UserInterface $user): bool
    {
        // Seuls les administrateurs peuvent supprimer une prise de vue
        return false;
    }
}