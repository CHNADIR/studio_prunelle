<?php

namespace App\Controller\Admin;

use App\Entity\TypeVente;
use App\Enum\TypeVenteEnum;
use App\Form\TypeVenteType;
use App\Repository\TypeVenteRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/type-vente')]
#[IsGranted('ROLE_ADMIN')]
class TypeVenteController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return TypeVente::class;
    }

    protected function getEnumClass(): string
    {
        return TypeVenteEnum::class;
    }

    protected function getFormClass(): string
    {
        return TypeVenteType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_type_vente';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/type_vente';
    }

    protected function getEntityDisplayName(): string
    {
        return 'type de vente';
    }

    #[Route('/', name: 'admin_type_vente_index', methods: ['GET'])]
    public function index(Request $request, TypeVenteRepository $typeVenteRepository): Response
    {
        return $this->handleIndex($request, $typeVenteRepository);
    }

    #[Route('/new', name: 'admin_type_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_type_vente_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_type_vente_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, TypeVenteRepository $typeVenteRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $typeVenteRepository);
    }

    #[Route('/{id}', name: 'admin_type_vente_show', methods: ['GET'])]
    public function show(TypeVente $typeVente): Response
    {
        return $this->handleShow($typeVente);
    }

    #[Route('/{id}/edit', name: 'admin_type_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeVente $typeVente): Response
    {
        return $this->handleEdit($request, $typeVente);
    }

    #[Route('/{id}', name: 'admin_type_vente_delete', methods: ['POST'])]
    public function delete(Request $request, TypeVente $typeVente): Response
    {
        return $this->handleDelete($request, $typeVente);
    }

    #[Route('/api/info', name: 'admin_type_vente_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_type_vente_api_list', methods: ['GET'])]
    public function getApiList(TypeVenteRepository $typeVenteRepository): JsonResponse
    {
        return $this->handleApiList($typeVenteRepository);
    }
}