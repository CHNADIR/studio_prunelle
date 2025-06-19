<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EcoleVoter extends Voter
{
    // Constantes d'attributs
    public const ECOLE_VIEW = 'ECOLE_VIEW';
    public const ECOLE_EDIT = 'ECOLE_EDIT';
    public const ECOLE_DELETE = 'ECOLE_DELETE';
    
    private Security $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifier si l'attribut est supporté
        return in_array($attribute, [self::ECOLE_VIEW, self::ECOLE_EDIT, self::ECOLE_DELETE])
            && $subject instanceof Ecole;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // Si l'utilisateur n'est pas connecté, refuser
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // SuperAdmin a tous les droits
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }
        
        // Admin a tous les droits sur les écoles
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        /** @var Ecole $ecole */
        $ecole = $subject;
        
        // Appliquer la logique en fonction de l'attribut
        switch ($attribute) {
            case self::ECOLE_VIEW:
                return $this->canView($ecole, $user);
            case self::ECOLE_EDIT:
                return $this->canEdit($ecole, $user);
            case self::ECOLE_DELETE:
                return $this->canDelete($ecole, $user);
        }
        
        return false;
    }
    
    private function canView(Ecole $ecole, UserInterface $user): bool
    {
        // Photographe peut voir une école s'il a une prise de vue associée
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && $user instanceof User) {
            // Vérification que l'école a au moins une prise de vue de ce photographe
            foreach ($ecole->getPrisesDeVue() as $priseDeVue) {
                if ($priseDeVue->getPhotographe() === $user) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function canEdit(Ecole $ecole, UserInterface $user): bool
    {
        // Seuls Admin et SuperAdmin peuvent modifier
        return false;
    }
    
    private function canDelete(Ecole $ecole, UserInterface $user): bool
    {
        // Seuls Admin et SuperAdmin peuvent supprimer
        return false;
    }
}