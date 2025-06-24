<?php

namespace App\Controller\Admin;

use App\Entity\PochetteIndiv;
use App\Enum\PochetteIndivEnum;
use App\Form\PochetteIndivType;
use App\Repository\PochetteIndivRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/pochette-indiv')]
#[IsGranted('ROLE_ADMIN')]
class PochetteIndivController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return PochetteIndiv::class;
    }

    protected function getEnumClass(): string
    {
        return PochetteIndivEnum::class;
    }

    protected function getFormClass(): string
    {
        return PochetteIndivType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_pochette_indiv';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/pochette_indiv';
    }

    protected function getEntityDisplayName(): string
    {
        return 'pochette individuelle';
    }

    #[Route('/', name: 'admin_pochette_indiv_index', methods: ['GET'])]
    public function index(Request $request, PochetteIndivRepository $pochetteIndivRepository): Response
    {
        return $this->handleIndex($request, $pochetteIndivRepository);
    }

    #[Route('/new', name: 'admin_pochette_indiv_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_pochette_indiv_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_pochette_indiv_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, PochetteIndivRepository $pochetteIndivRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $pochetteIndivRepository);
    }

    #[Route('/{id}', name: 'admin_pochette_indiv_show', methods: ['GET'])]
    public function show(PochetteIndiv $pochetteIndiv): Response
    {
        return $this->handleShow($pochetteIndiv);
    }

    #[Route('/{id}/edit', name: 'admin_pochette_indiv_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PochetteIndiv $pochetteIndiv): Response
    {
        return $this->handleEdit($request, $pochetteIndiv);
    }

    #[Route('/{id}', name: 'admin_pochette_indiv_delete', methods: ['POST'])]
    public function delete(Request $request, PochetteIndiv $pochetteIndiv): Response
    {
        return $this->handleDelete($request, $pochetteIndiv);
    }

    #[Route('/api/info', name: 'admin_pochette_indiv_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_pochette_indiv_api_list', methods: ['GET'])]
    public function getApiList(PochetteIndivRepository $pochetteIndivRepository): JsonResponse
    {
        return $this->handleApiList($pochetteIndivRepository);
    }
} 