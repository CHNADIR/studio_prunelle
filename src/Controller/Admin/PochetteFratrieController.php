<?php

namespace App\Controller\Admin;

use App\Entity\PochetteFratrie;
use App\Enum\PochetteFratrieEnum;
use App\Form\PochetteFratrieType;
use App\Repository\PochetteFratrieRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/pochette-fratrie')]
#[IsGranted('ROLE_ADMIN')]
class PochetteFratrieController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return PochetteFratrie::class;
    }

    protected function getEnumClass(): string
    {
        return PochetteFratrieEnum::class;
    }

    protected function getFormClass(): string
    {
        return PochetteFratrieType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_pochette_fratrie';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/pochette_fratrie';
    }

    protected function getEntityDisplayName(): string
    {
        return 'pochette fratrie';
    }

    #[Route('/', name: 'admin_pochette_fratrie_index', methods: ['GET'])]
    public function index(Request $request, PochetteFratrieRepository $pochetteFratrieRepository): Response
    {
        return $this->handleIndex($request, $pochetteFratrieRepository);
    }

    #[Route('/new', name: 'admin_pochette_fratrie_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_pochette_fratrie_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_pochette_fratrie_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, PochetteFratrieRepository $pochetteFratrieRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $pochetteFratrieRepository);
    }

    #[Route('/{id}', name: 'admin_pochette_fratrie_show', methods: ['GET'])]
    public function show(PochetteFratrie $pochetteFratrie): Response
    {
        return $this->handleShow($pochetteFratrie);
    }

    #[Route('/{id}/edit', name: 'admin_pochette_fratrie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PochetteFratrie $pochetteFratrie): Response
    {
        return $this->handleEdit($request, $pochetteFratrie);
    }

    #[Route('/{id}', name: 'admin_pochette_fratrie_delete', methods: ['POST'])]
    public function delete(Request $request, PochetteFratrie $pochetteFratrie): Response
    {
        return $this->handleDelete($request, $pochetteFratrie);
    }

    #[Route('/api/info', name: 'admin_pochette_fratrie_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_pochette_fratrie_api_list', methods: ['GET'])]
    public function getApiList(PochetteFratrieRepository $pochetteFratrieRepository): JsonResponse
    {
        return $this->handleApiList($pochetteFratrieRepository);
    }
} 