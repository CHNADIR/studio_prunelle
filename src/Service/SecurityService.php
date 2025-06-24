<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Service de sécurité centralisé
 * Patterns : Security Layer, Logging, Rate Limiting
 */
class SecurityService
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes
    
    public function __construct(
        private Security $security,
        private LoggerInterface $logger
    ) {}

    /**
     * Vérifie les tentatives de connexion pour prévenir le brute force
     */
    public function checkLoginAttempts(string $identifier): bool
    {
        $cacheKey = 'login_attempts_' . md5($identifier);
        
        // Dans un vrai projet, utiliser Redis ou cache Symfony
        // Ici simulation basique
        $sessionFile = sys_get_temp_dir() . '/' . $cacheKey;
        
        if (file_exists($sessionFile)) {
            $data = json_decode(file_get_contents($sessionFile), true);
            
            if ($data['attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
                if (time() - $data['last_attempt'] < self::LOCKOUT_DURATION) {
                    $this->logger->warning('Login blocked due to too many attempts', [
                        'identifier' => $identifier,
                        'attempts' => $data['attempts']
                    ]);
                    return false;
                }
                
                // Réinitialiser après expiration
                unlink($sessionFile);
            }
        }
        
        return true;
    }

    /**
     * Enregistre une tentative de connexion échouée
     */
    public function recordFailedLogin(string $identifier): void
    {
        $cacheKey = 'login_attempts_' . md5($identifier);
        $sessionFile = sys_get_temp_dir() . '/' . $cacheKey;
        
        $data = ['attempts' => 1, 'last_attempt' => time()];
        
        if (file_exists($sessionFile)) {
            $existingData = json_decode(file_get_contents($sessionFile), true);
            $data['attempts'] = $existingData['attempts'] + 1;
        }
        
        file_put_contents($sessionFile, json_encode($data));
        
        $this->logger->warning('Failed login attempt recorded', [
            'identifier' => $identifier,
            'attempts' => $data['attempts']
        ]);
    }

    /**
     * Nettoie les tentatives après connexion réussie
     */
    public function clearLoginAttempts(string $identifier): void
    {
        $cacheKey = 'login_attempts_' . md5($identifier);
        $sessionFile = sys_get_temp_dir() . '/' . $cacheKey;
        
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
    }

    /**
     * Vérifie les permissions d'accès selon les patterns de sécurité
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return false;
        }

        // Mapping des permissions par rôle
        $permissions = [
            'ROLE_PHOTOGRAPHE' => ['view_prise_de_vue', 'create_prise_de_vue', 'edit_own_prise_de_vue'],
            'ROLE_ADMIN' => ['*'], // Toutes les permissions
            'ROLE_SUPERADMIN' => ['*', 'manage_users', 'system_admin']
        ];

        foreach ($user->getRoles() as $role) {
            if (isset($permissions[$role])) {
                if (in_array('*', $permissions[$role]) || in_array($permission, $permissions[$role])) {
                    return true;
                }
            }
        }

        $this->logger->warning('Permission denied', [
            'user' => $user->getUserIdentifier(),
            'permission' => $permission,
            'roles' => $user->getRoles()
        ]);

        return false;
    }

    /**
     * Nettoie et valide les entrées utilisateur pour prévenir les injections
     */
    public function sanitizeInput(string $input, string $type = 'text'): string
    {
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return (string) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return (string) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    /**
     * Génère un token CSRF sécurisé
     */
    public function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Vérifie la force d'un mot de passe
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial';
        }
        
        return $errors;
    }

    /**
     * Log les actions sensibles pour audit
     */
    public function logSensitiveAction(string $action, array $context = []): void
    {
        $user = $this->security->getUser();
        
        $this->logger->info('Sensitive action performed', [
            'action' => $action,
            'user' => $user ? $user->getUserIdentifier() : 'anonymous',
            'user_roles' => $user ? $user->getRoles() : [],
            'timestamp' => time(),
            'context' => $context
        ]);
    }
} 