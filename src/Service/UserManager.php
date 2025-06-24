<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service de gestion des utilisateurs
 * Encapsule la logique métier selon le Service Layer Pattern
 * Pattern appliqué: Service Layer + Security Logic (patterns.md)
 */
class UserManager
{
    private string $adminEmail;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly MailerInterface $mailer,
        string $adminEmail = 'admin@studio-prunelle.fr'
    ) {
        $this->adminEmail = $adminEmail;
    }

    /**
     * Sauvegarde un utilisateur avec hashage du mot de passe si fourni
     */
    public function save(User $user, ?string $plainPassword = null): array
    {
        // Validation de l'entité
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Hashage du mot de passe si fourni
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return [
                'success' => true,
                'entity' => $user
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Erreur lors de la sauvegarde : ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur et envoie un email si demandé
     * Pattern: Service Layer - logique métier centralisée avec notification
     */
    public function resetPassword(string $email, bool $sendEmail = true): array
    {
        try {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'errors' => ['Utilisateur non trouvé avec l\'email: ' . $email]
                ];
            }
            
            // Générer un mot de passe aléatoire sécurisé
            $newPassword = $this->generateSecurePassword();
            
            // Hasher le mot de passe avec Argon2id
            $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
            
            // Mettre à jour l'utilisateur
            $user->setPassword($hashedPassword);
            $this->entityManager->flush();
            
            // Envoyer l'email si demandé
            if ($sendEmail) {
                $this->sendPasswordResetEmail($user, $newPassword);
            }
            
            return [
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'new_password' => $newPassword // Pour les tests, à retirer en production
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Erreur lors de la réinitialisation : ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Supprime un utilisateur avec vérifications de sécurité
     */
    public function delete(User $user): array
    {
        // Vérifier si c'est le dernier SuperAdmin
        if ($this->isLastSuperAdmin($user)) {
            return [
                'success' => false,
                'errors' => ['Impossible de supprimer le dernier SuperAdmin.']
            ];
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Erreur lors de la suppression : ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Vérifie si l'utilisateur est le dernier SuperAdmin
     */
    public function isLastSuperAdmin(User $user): bool
    {
        if (!$user->hasRole('ROLE_SUPERADMIN')) {
            return false;
        }

        $superAdmins = $this->userRepository->findByRole('ROLE_SUPERADMIN');
        return count($superAdmins) === 1 && $superAdmins[0]->getId() === $user->getId();
    }

    /**
     * Trouve tous les utilisateurs avec pagination
     */
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->userRepository->findAllPaginated($page, $limit);
    }

    /**
     * Crée un nouvel utilisateur avec des valeurs par défaut
     */
    public function createNew(): User
    {
        $user = new User();
        $user->setRoles(['ROLE_PHOTOGRAPHE']); // Rôle par défaut
        return $user;
    }

    /**
     * Vérifie si un utilisateur peut être supprimé
     */
    public function canDelete(User $user): bool
    {
        return !$this->isLastSuperAdmin($user);
    }

    /**
     * Met à jour les rôles d'un utilisateur avec vérifications
     */
    public function updateRoles(User $user, array $newRoles, User $currentUser): array
    {
        // Empêcher de retirer son propre rôle superadmin si c'est le dernier
        if ($user === $currentUser 
            && $this->isLastSuperAdmin($user) 
            && !in_array('ROLE_SUPERADMIN', $newRoles)
        ) {
            return [
                'success' => false,
                'errors' => ['Impossible de retirer le dernier rôle SuperAdmin.']
            ];
        }

        $user->setRoles($newRoles);
        return $this->save($user);
    }

    /**
     * Génère un mot de passe aléatoire sécurisé
     * Pattern: Security Layer - génération sécurisée
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Envoie un email avec le nouveau mot de passe
     * Pattern: Service Layer - notification centralisée
     */
    private function sendPasswordResetEmail(User $user, string $password): void
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe - Studio Prunelle')
            ->html(
                $this->getEmailTemplate($user->getNom(), $password)
            );
        
        $this->mailer->send($email);
    }
    
    /**
     * Retourne le template d'email pour la réinitialisation du mot de passe
     * Pattern: Template Method - génération de contenu
     */
    private function getEmailTemplate(string $name, string $password): string
    {
        return <<<HTML
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4285f4; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .password { background-color: #f5f5f5; padding: 10px; margin: 20px 0; font-family: monospace; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Studio Prunelle</h1>
                    </div>
                    <div class="content">
                        <h2>Réinitialisation de mot de passe</h2>
                        <p>Bonjour $name,</p>
                        <p>Votre mot de passe a été réinitialisé. Voici votre nouveau mot de passe :</p>
                        <div class="password">$password</div>
                        <p>Nous vous recommandons de le changer dès votre prochaine connexion.</p>
                        <p>Cordialement,<br>L'équipe Studio Prunelle</p>
                    </div>
                    <div class="footer">
                        <p>Ce message est automatique, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        HTML;
    }
}