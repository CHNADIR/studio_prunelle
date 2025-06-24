<?php

namespace App\Controller\Admin;

use App\Service\ReferentialManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractReferentialController extends AbstractController
{
    public function __construct(
        protected ReferentialManager $referentialManager
    ) {}

    abstract protected function getEntityClass(): string;

    abstract protected function getEnumClass(): string;

    abstract protected function getFormClass(): string;

    abstract protected function getRoutePrefix(): string;

    abstract protected function getTemplatePrefix(): string;

    abstract protected function getEntityDisplayName(): string;

    protected function handleIndex(Request $request, object $repository): Response
    {
        $search = $request->query->get('search');
        $active = $request->query->get('active');
        $sort = $request->query->get('sort', 'libelle');
        $order = $request->query->get('order', 'ASC');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $showType = $request->query->get('type', 'all');

        if ($active !== null) {
            $active = $active === '1';
        }

        if ($showType === 'enum') {
            $entities = $repository->findEnumBasedTypes();
            $total = count($entities);
        } elseif ($showType === 'custom') {
            $entities = $repository->findCustomTypes();
            $total = count($entities);
        } else {
            $entities = $repository->findByCriteria($search, $active, $sort, $order, $page, $limit);
            $total = $repository->countByCriteria($search, $active);
        }

        $enumInfo = $this->getEnumInfo();
        
        $entityVariableName = $this->getEntityVariableName();

        return $this->render($this->getTemplatePrefix() . '/index.html.twig', [
            $entityVariableName => $entities,
            'enum_info' => $enumInfo,
            'search' => $search,
            'active' => $active,
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'show_type' => $showType,
            'total_pages' => ceil($total / $limit)
        ]);
    }

    protected function handleNew(): Response
    {
        return $this->redirectToRoute($this->getRoutePrefix() . '_quick_new');
    }

    protected function handleQuickNew(Request $request): Response
    {
        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();
        $entity->setActive(true);
        
        $formClass = $this->getFormClass();
        $form = $this->createForm($formClass, $entity, [
            'submit_label' => 'Créer le ' . $this->getEntityDisplayName()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($entity);

            if ($result['success']) {
                $this->addFlash('success', 'Le nouveau ' . $this->getEntityDisplayName() . ' a été créé avec succès !');
                
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => ucfirst($this->getEntityDisplayName()) . ' créé avec succès',
                        strtolower(str_replace(' ', '', $this->getEntityDisplayName())) => [
                            'id' => $entity->getId(),
                            'libelle' => $entity->getLibelle(),
                            'description' => $entity->getDescription()
                        ]
                    ]);
                }
                
                return $this->redirectToRoute($this->getRoutePrefix() . '_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', is_string($error) ? $error : $error->getMessage());
                }
            }
        }

        return $this->render($this->getTemplatePrefix() . '/quick_new.html.twig', [
            strtolower(str_replace(' ', '_', $this->getEntityDisplayName())) => $entity,
            'form' => $form->createView()
        ]);
    }

    protected function handleCreateFromEnum(string $enumValue, object $repository): JsonResponse
    {
        try {
            $enumClass = $this->getEnumClass();
            
            $enumCase = null;
            foreach ($enumClass::cases() as $case) {
                if ($case->value === $enumValue) {
                    $enumCase = $case;
                    break;
                }
            }

            if (!$enumCase) {
                return new JsonResponse(['success' => false, 'message' => 'Valeur d\'enum invalide'], 400);
            }

            $entity = $repository->findOrCreateFromEnum($enumCase);

            return new JsonResponse([
                'success' => true,
                'id' => $entity->getId(),
                'libelle' => $entity->getLibelle(),
                'description' => $entity->getDescription(),
                'is_enum' => true,
                'message' => ucfirst($this->getEntityDisplayName()) . ' créé depuis les valeurs standards'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function handleShow(object $entity): Response
    {
        $isEnumValue = $this->referentialManager->isEnumValue($entity);
        
        return $this->render($this->getTemplatePrefix() . '/show.html.twig', [
            strtolower(str_replace(' ', '_', $this->getEntityDisplayName())) => $entity,
            'is_enum_value' => $isEnumValue,
            'enum_info' => $isEnumValue ? $this->getEnumInfoForValue($entity->getLibelle()) : null
        ]);
    }

    protected function handleEdit(Request $request, object $entity): Response
    {
        $isEnumValue = $this->referentialManager->isEnumValue($entity);
        
        $formClass = $this->getFormClass();
        $form = $this->createForm($formClass, $entity, [
            'show_submit' => false,
            'autofocus' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->referentialManager->save($entity);

            if ($result['success']) {
                $message = $isEnumValue 
                    ? 'Le ' . $this->getEntityDisplayName() . ' standard a été modifié.'
                    : 'Le ' . $this->getEntityDisplayName() . ' personnalisé a été modifié avec succès.';
                    
                $this->addFlash('success', $message);
                return $this->redirectToRoute($this->getRoutePrefix() . '_index');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', is_string($error) ? $error : $error->getMessage());
                }
            }
        }

        return $this->render($this->getTemplatePrefix() . '/edit.html.twig', [
            strtolower(str_replace(' ', '_', $this->getEntityDisplayName())) => $entity,
            'form' => $form->createView(),
            'is_enum_value' => $isEnumValue
        ]);
    }

    protected function handleDelete(Request $request, object $entity): Response
    {
        if ($this->isCsrfTokenValid('delete' . $entity->getId(), $request->request->get('_token'))) {
            $result = $this->referentialManager->delete($entity);
            
            if ($result['success']) {
                $this->addFlash('success', 'Le ' . $this->getEntityDisplayName() . ' a été supprimé avec succès.');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', is_string($error) ? $error : $error->getMessage());
                }
            }
        }

        return $this->redirectToRoute($this->getRoutePrefix() . '_index');
    }

    protected function handleApiInfo(): JsonResponse
    {
        return new JsonResponse($this->getEnumInfo());
    }

    protected function handleApiList(object $repository): JsonResponse
    {
        $entities = $repository->findAllActive();
        
        $data = array_map(function($entity) {
            return [
                'id' => $entity->getId(),
                'libelle' => $entity->getLibelle(),
                'description' => $entity->getDescription(),
                'active' => $entity->isActive()
            ];
        }, $entities);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    }

    protected function handleModalNew(
        Request $request, 
        object $entity,
        FormInterface $form,
        string $template,
        string $successMessage,
        string $redirectRoute
    ): Response {
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager = $this->container->get('doctrine.orm.entity_manager');
                $entityManager->persist($entity);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'id' => $entity->getId(),
                        'text' => method_exists($entity, 'getLibelle') ? $entity->getLibelle() : $entity->__toString(),
                        'message' => $successMessage
                    ]);
                }

                $this->addFlash('success', $successMessage);
                return $this->redirectToRoute($redirectRoute);
            } else {
                if ($request->isXmlHttpRequest()) {
                    $errors = [];
                    foreach ($form->getErrors(true, false) as $error) {
                        $errors[] = $error->getMessage();
                    }
                    
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $errors
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        return $this->render($template, [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    protected function getEnumInfo(): array
    {
        $enumClass = $this->getEnumClass();
        $enumInfo = [];
        
        foreach ($enumClass::getDefaultValues() as $enum) {
            $enumInfo[] = [
                'value' => $enum->value,
                'libelle' => $enum->getLibelle(),
                'description' => $enum->getDescription()
            ];
        }
        
        return $enumInfo;
    }

    protected function getEnumInfoForValue(string $libelle): ?array
    {
        $enumClass = $this->getEnumClass();
        
        foreach ($enumClass::getDefaultValues() as $enum) {
            if ($enum->getLibelle() === $libelle) {
                return [
                    'value' => $enum->value,
                    'libelle' => $enum->getLibelle(),
                    'description' => $enum->getDescription()
                ];
            }
        }
        
        return null;
    }

    protected function getEntityVariableName(): string
    {
        $entityName = $this->getEntityDisplayName();
        
        $mapping = [
            'thème' => 'themes',
            'planche' => 'planches', 
            'pochette individuelle' => 'pochettes',
            'pochette fratrie' => 'pochettes',
            'type de prise' => 'typeprises',
            'type de vente' => 'typeventes',
            'école' => 'ecoles',
            'utilisateur' => 'users',
            'prise de vue' => 'prisesdevue'
        ];
        
        return $mapping[$entityName] ?? strtolower(str_replace(' ', '', $entityName)) . 's';
    }
}