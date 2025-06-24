<?php

namespace App\Controller\Admin;

use App\Entity\Planche;
use App\Enum\PlancheEnum;
use App\Form\PlancheType;
use App\Repository\PlancheRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/planche')]
#[IsGranted('ROLE_ADMIN')]
class PlancheController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return Planche::class;
    }

    protected function getEnumClass(): string
    {
        return PlancheEnum::class;
    }

    protected function getFormClass(): string
    {
        return PlancheType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_planche';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/planche';
    }

    protected function getEntityDisplayName(): string
    {
        return 'planche';
    }

    #[Route('/', name: 'admin_planche_index', methods: ['GET'])]
    public function index(Request $request, PlancheRepository $plancheRepository): Response
    {
        return $this->handleIndex($request, $plancheRepository);
    }

    #[Route('/new', name: 'admin_planche_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_planche_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_planche_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, PlancheRepository $plancheRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $plancheRepository);
    }

    #[Route('/{id}', name: 'admin_planche_show', methods: ['GET'])]
    public function show(Planche $planche): Response
    {
        return $this->handleShow($planche);
    }

    #[Route('/{id}/edit', name: 'admin_planche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Planche $planche): Response
    {
        return $this->handleEdit($request, $planche);
    }

    #[Route('/{id}', name: 'admin_planche_delete', methods: ['POST'])]
    public function delete(Request $request, Planche $planche): Response
    {
        return $this->handleDelete($request, $planche);
    }

    #[Route('/api/info', name: 'admin_planche_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_planche_api_list', methods: ['GET'])]
    public function getApiList(PlancheRepository $plancheRepository): JsonResponse
    {
        return $this->handleApiList($plancheRepository);
    }

    #[Route('/api/stats', name: 'admin_planche_api_stats', methods: ['GET'])]
    public function getStats(PlancheRepository $plancheRepository): JsonResponse
    {
        $stats = $plancheRepository->getPlancheStats();
        
        return new JsonResponse([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
