<?php

namespace App\Controller\Admin;

use App\Entity\TypePrise;
use App\Enum\TypePriseEnum;
use App\Form\TypePriseType;
use App\Repository\TypePriseRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/type-prise')]
#[IsGranted('ROLE_ADMIN')]
class TypePriseController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return TypePrise::class;
    }

    protected function getEnumClass(): string
    {
        return TypePriseEnum::class;
    }

    protected function getFormClass(): string
    {
        return TypePriseType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_type_prise';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/type_prise';
    }

    protected function getEntityDisplayName(): string
    {
        return 'type de prise';
    }

    #[Route('/', name: 'admin_type_prise_index', methods: ['GET'])]
    public function index(Request $request, TypePriseRepository $typePriseRepository): Response
    {
        return $this->handleIndex($request, $typePriseRepository);
    }

    #[Route('/new', name: 'admin_type_prise_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_type_prise_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_type_prise_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, TypePriseRepository $typePriseRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $typePriseRepository);
    }

    #[Route('/{id}', name: 'admin_type_prise_show', methods: ['GET'])]
    public function show(TypePrise $typePrise): Response
    {
        return $this->handleShow($typePrise);
    }

    #[Route('/{id}/edit', name: 'admin_type_prise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypePrise $typePrise): Response
    {
        return $this->handleEdit($request, $typePrise);
    }

    #[Route('/{id}', name: 'admin_type_prise_delete', methods: ['POST'])]
    public function delete(Request $request, TypePrise $typePrise): Response
    {
        return $this->handleDelete($request, $typePrise);
    }

    #[Route('/api/info', name: 'admin_type_prise_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_type_prise_api_list', methods: ['GET'])]
    public function getApiList(TypePriseRepository $typePriseRepository): JsonResponse
    {
        return $this->handleApiList($typePriseRepository);
    }
}