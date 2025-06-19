<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserPasswordService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private MailerInterface $mailer;
    private string $adminEmail;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        string $adminEmail = 'admin@studio-prunelle.fr'
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }
    
    /**
     * Réinitialise le mot de passe d'un utilisateur et envoie un email si demandé
     */
    public function resetPassword(string $email, bool $sendEmail = true): string
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        
        if (!$user) {
            throw new UserNotFoundException('Utilisateur non trouvé avec l\'email: ' . $email);
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
        
        return $newPassword;
    }
    
    /**
     * Génère un mot de passe aléatoire sécurisé
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