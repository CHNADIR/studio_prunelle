<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Enum\ThemeEnum;
use App\Form\ThemeType;
use App\Repository\ThemeRepository;
use App\Service\ReferentialManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/theme')]
#[IsGranted('ROLE_ADMIN')]
class ThemeController extends AbstractReferentialController
{
    public function __construct(ReferentialManager $referentialManager)
    {
        parent::__construct($referentialManager);
    }

    protected function getEntityClass(): string
    {
        return Theme::class;
    }

    protected function getEnumClass(): string
    {
        return ThemeEnum::class;
    }

    protected function getFormClass(): string
    {
        return ThemeType::class;
    }

    protected function getRoutePrefix(): string
    {
        return 'admin_theme';
    }

    protected function getTemplatePrefix(): string
    {
        return 'admin/theme';
    }

    protected function getEntityDisplayName(): string
    {
        return 'thème';
    }

    #[Route('/', name: 'admin_theme_index', methods: ['GET'])]
    public function index(Request $request, ThemeRepository $themeRepository): Response
    {
        return $this->handleIndex($request, $themeRepository);
    }

    #[Route('/new', name: 'admin_theme_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleNew();
    }

    #[Route('/quick-new', name: 'admin_theme_quick_new', methods: ['GET', 'POST'])]
    public function quickNew(Request $request): Response
    {
        return $this->handleQuickNew($request);
    }

    #[Route('/enum/{enumValue}', name: 'admin_theme_from_enum', methods: ['POST'])]
    public function createFromEnum(string $enumValue, ThemeRepository $themeRepository): JsonResponse
    {
        return $this->handleCreateFromEnum($enumValue, $themeRepository);
    }

    #[Route('/{id}', name: 'admin_theme_show', methods: ['GET'])]
    public function show(Theme $theme): Response
    {
        return $this->handleShow($theme);
    }

    #[Route('/{id}/edit', name: 'admin_theme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Theme $theme): Response
    {
        return $this->handleEdit($request, $theme);
    }

    #[Route('/{id}', name: 'admin_theme_delete', methods: ['POST'])]
    public function delete(Request $request, Theme $theme): Response
    {
        return $this->handleDelete($request, $theme);
    }

    #[Route('/api/info', name: 'admin_theme_api_info', methods: ['GET'])]
    public function getApiInfo(): JsonResponse
    {
        return $this->handleApiInfo();
    }

    #[Route('/api/list', name: 'admin_theme_api_list', methods: ['GET'])]
    public function getApiList(ThemeRepository $themeRepository): JsonResponse
    {
        return $this->handleApiList($themeRepository);
    }
}