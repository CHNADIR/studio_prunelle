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
    // Actions possibles - Utilisation de constantes standardisées
    const PRISEDEVUE_VIEW = 'PRISEDEVUE_VIEW';
    const PRISEDEVUE_EDIT = 'PRISEDEVUE_EDIT';
    const PRISEDEVUE_DELETE = 'PRISEDEVUE_DELETE';
    const PRISEDEVUE_CREATE = 'PRISEDEVUE_CREATE';
    
    // Pour la rétrocompatibilité
    const VIEW = self::PRISEDEVUE_VIEW;
    const EDIT = self::PRISEDEVUE_EDIT; 
    const DELETE = self::PRISEDEVUE_DELETE;
    
    private Security $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifier si l'attribut est supporté (utiliser les nouvelles constantes)
        if (!in_array($attribute, [
            self::PRISEDEVUE_VIEW, 
            self::PRISEDEVUE_EDIT, 
            self::PRISEDEVUE_DELETE,
            self::PRISEDEVUE_CREATE,
            self::VIEW,  // Rétrocompatibilité
            self::EDIT,  // Rétrocompatibilité
            self::DELETE // Rétrocompatibilité
        ])) {
            return false;
        }
        
        // Pour CREATE, aucun sujet n'est nécessaire
        if ($attribute === self::PRISEDEVUE_CREATE) {
            return true;
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
        
        // Normalisation des attributs pour utiliser les nouvelles constantes
        if ($attribute === self::VIEW) {
            $attribute = self::PRISEDEVUE_VIEW;
        } elseif ($attribute === self::EDIT) {
            $attribute = self::PRISEDEVUE_EDIT;
        } elseif ($attribute === self::DELETE) {
            $attribute = self::PRISEDEVUE_DELETE;
        }
        
        // Cas spécial pour CREATE - vérifier seulement les rôles
        if ($attribute === self::PRISEDEVUE_CREATE) {
            return $this->security->isGranted('ROLE_ADMIN');
        }
        
        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;
        
        // Vérifier le droit spécifique
        switch ($attribute) {
            case self::PRISEDEVUE_VIEW:
                return $this->canView($priseDeVue, $user);
            case self::PRISEDEVUE_EDIT:
                return $this->canEdit($priseDeVue, $user);
            case self::PRISEDEVUE_DELETE:
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