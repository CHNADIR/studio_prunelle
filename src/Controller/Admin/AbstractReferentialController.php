<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Contrôleur abstrait pour les référentiels (TypePrise, TypeVente, Theme)
 * Factorise la logique commune des ajouts modaux
 */
abstract class AbstractReferentialController extends AbstractController
{
    /**
     * Traitement générique pour l'ajout modal d'un référentiel
     */
    protected function handleModalNew(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        object $entity,
        FormInterface $form,
        string $template,
        string $successMessage,
        string $redirectRoute
    ): Response {
        // Traiter le formulaire
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($entity);
                $entityManager->flush();

                // Traitement AJAX
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'id' => $entity->getId(),
                        'text' => $entity->getNom(),
                        'message' => $successMessage
                    ]);
                }

                // Traitement standard
                $this->addFlash('success', $successMessage);
                return $this->redirectToRoute($redirectRoute);
            } 
            // Gestion des erreurs
            else {
                if ($request->isXmlHttpRequest()) {
                    $errors = [];
                    foreach ($validator->validate($entity) as $error) {
                        $errors[] = $error->getMessage();
                    }
                    
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $errors
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Rendre le template pour le formulaire modal
        return $this->render($template, [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }
}