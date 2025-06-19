<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Service pour gérer les messages flash de façon standardisée
 */
class FlashMessageService
{
    private RequestStack $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    /**
     * Ajoute un message de succès
     */
    public function addSuccess(string $message): void
    {
        $this->addMessage('success', $message);
    }
    
    /**
     * Ajoute un message d'erreur
     */
    public function addError(string $message): void
    {
        $this->addMessage('error', $message);
    }
    
    /**
     * Ajoute un message d'information
     */
    public function addInfo(string $message): void
    {
        $this->addMessage('info', $message);
    }
    
    /**
     * Ajoute un message d'avertissement
     */
    public function addWarning(string $message): void
    {
        $this->addMessage('warning', $message);
    }
    
    /**
     * Ajoute un message de confirmation qui nécessite une action
     */
    public function addConfirmation(string $message, string $confirmationRoute, array $routeParams = []): void
    {
        $this->getFlashBag()->add('confirmation', [
            'message' => $message,
            'route' => $confirmationRoute,
            'params' => $routeParams
        ]);
    }
    
    /**
     * Méthode privée pour ajouter un message flash
     */
    private function addMessage(string $type, string $message): void
    {
        $this->getFlashBag()->add($type, $message);
    }
    
    /**
     * Récupère le flash bag
     */
    private function getFlashBag(): FlashBagInterface
    {
        return $this->requestStack->getSession()->getFlashBag();
    }
}