<?php

namespace App\Controller;

use App\Controller\Abstract\AbstractCrudController; // MODIFIÉ
use App\Entity\Planche;
use App\Form\PlancheForm;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface; // Nécessaire pour le constructeur parent
use Knp\Component\Pager\PaginatorInterface; // Nécessaire pour le constructeur parent
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormInterface; // Pour les types des hooks

#[Route('/planche')]
final class PlancheController extends AbstractCrudController // MODIFIÉ
{
    private UploaderHelper $uploaderHelper;
    private ?string $filenameToDelete = null; // Pour stocker le nom du fichier à supprimer

    // Surcharger le constructeur pour injecter UploaderHelper et appeler le parent
    public function __construct(
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        UploaderHelper $uploaderHelper
    ) {
        parent::__construct($entityManager, $paginator);
        $this->uploaderHelper = $uploaderHelper;
    }

    // --- Implémentation des méthodes abstraites du parent ---
    protected function getEntityClass(): string
    {
        return Planche::class;
    }

    protected function getFormClass(): string
    {
        return PlancheForm::class;
    }

    protected function getIndexRouteName(): string
    {
        return 'app_planche_index';
    }

    protected function getTemplateBasePath(): string
    {
        return 'planche';
    }

    protected function getEntityNameSingular(): string
    {
        return 'planche';
    }

    protected function getEntityNamePlural(): string
    {
        return 'planches';
    }

    // Surcharger pour correspondre à l'alias utilisé dans les templates KNP Paginator
    protected function getEntityAlias(): string
    {
        return 'p';
    }

    // Surcharger pour définir les champs sur lesquels la recherche s'applique
    protected function getSearchableFields(): array
    {
        return ['nom', 'categorie'];
    }

    // --- Hooks pour la gestion des images ---

    protected function beforePersist(object $entity, FormInterface $form, Request $request): void
    {
        /** @var Planche $entity */
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $newFilename = $this->uploaderHelper->uploadPlancheImage($imageFile);
            $entity->setImageFilename($newFilename);
        }
    }

    protected function beforeUpdate(object $entity, FormInterface $form, Request $request): void
    {
        /** @var Planche $entity */
        $existingImage = $entity->getImageFilename(); // Sauvegarder le nom de l'image existante

        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $newFilename = $this->uploaderHelper->uploadPlancheImage($imageFile, $existingImage);
            $entity->setImageFilename($newFilename);
        }
    }

    protected function beforeRemove(object $entity, Request $request): void
    {
        /** @var Planche $entity */
        if ($entity->getImageFilename()) {
            // Stocker le nom du fichier pour le supprimer dans afterRemove
            $this->filenameToDelete = $entity->getImageFilename();
        }
    }

    protected function afterRemove(object $entity, Request $request): void
    {
        if ($this->filenameToDelete) {
            $this->uploaderHelper->removePlancheImage($this->filenameToDelete);
            $this->filenameToDelete = null; // Réinitialiser pour la prochaine suppression potentielle
        }
    }

    // --- Actions publiques appelant les méthodes protégées du parent ---

    #[Route(name: 'app_planche_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // La sécurité est gérée par security.yaml ou un Voter si configuré
        // Exemple : $this->denyAccessUnlessGranted('ROLE_GESTION_PLANCHE');
        return $this->processIndex($request);
    }

    #[Route('/new', name: 'app_planche_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Exemple : $this->denyAccessUnlessGranted('ROLE_GESTION_PLANCHE');
        return $this->processNew($request);
    }

    #[Route('/{id}', name: 'app_planche_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        $planche = $this->entityManager->getRepository($this->getEntityClass())->find($id);

        if (!$planche) {
            throw $this->createNotFoundException('La planche n\'existe pas');
        }

        // Vérification explicite des droits avec le nouveau Voter
        $this->denyAccessUnlessGranted('PLANCHE_VIEW', $planche);

        return $this->render($this->getTemplateBasePath() . '/show.html.twig', [
            'planche' => $planche,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_planche_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response // L'entité est récupérée par processEdit
    {
        return $this->processEdit($request, $id);
    }

    #[Route('/{id}', name: 'app_planche_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, int $id): Response // L'entité est récupérée par processDelete
    {
        return $this->processDelete($request, $id);
    }

    // Optionnel: Surcharger les paramètres de rendu si besoin d'ajouter des variables spécifiques
    // pour les templates de Planche (par exemple, uploader_helper si nécessaire dans le template)
    /*
    protected function getRenderParametersForShow(object $entity): array
    {
        $params = parent::getRenderParametersForShow($entity);
        // $params['uploader_helper'] = $this->uploaderHelper; // Si vous en avez besoin directement dans le template show
        return $params;
    }

    protected function getRenderParametersForNewEdit(FormInterface $form, object $entity): array
    {
        $params = parent::getRenderParametersForNewEdit($form, $entity);
        // $params['uploader_helper'] = $this->uploaderHelper; // Si vous en avez besoin directement dans les templates new/edit
        return $params;
    }
    */
}
