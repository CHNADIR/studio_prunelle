<?php

namespace App\Controller;

use App\Entity\Planche;
use App\Form\PlancheForm;
use App\Repository\PlancheRepository;
use App\Service\UploaderHelper; // Ajoutez cette ligne
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile; // Ajoutez cette ligne
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planche')]
final class PlancheController extends AbstractController
{
    #[Route(name: 'app_planche_index', methods: ['GET'])]
    public function index(
        PlancheRepository $plancheRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $queryBuilder = $plancheRepository->createQueryBuilder('p');

        $search = $request->query->get('search');
        if ($search) {
            $queryBuilder->andWhere('p.nom LIKE :search OR p.categorie LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10, // Limite par page
            ['defaultSortFieldName' => 'p.nom', 'defaultSortDirection' => 'asc']
        );

        return $this->render('planche/index.html.twig', [
            'planches' => $pagination,
            'current_search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_planche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper): Response
    {
        $planche = new Planche();
        $form = $this->createForm(PlancheForm::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = $uploaderHelper->uploadPlancheImage($imageFile);
                $planche->setImageFilename($newFilename);
            }

            $entityManager->persist($planche);
            $entityManager->flush();
            $this->addFlash('success', 'Planche créée avec succès.');
            return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planche/new.html.twig', [
            'planche' => $planche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_planche_show', methods: ['GET'])]
    public function show(Planche $planche, UploaderHelper $uploaderHelper): Response
    {
        return $this->render('planche/show.html.twig', [
            'planche' => $planche,
            'uploader_helper' => $uploaderHelper, // Pour obtenir le chemin public
        ]);
    }

    #[Route('/{id}/edit', name: 'app_planche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Planche $planche, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper): Response
    {
        $existingImage = $planche->getImageFilename(); // Sauvegarder le nom de l'image existante
        $form = $this->createForm(PlancheForm::class, $planche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = $uploaderHelper->uploadPlancheImage($imageFile, $existingImage);
                $planche->setImageFilename($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Planche modifiée avec succès.');
            return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planche/edit.html.twig', [
            'planche' => $planche,
            'form' => $form,
            'uploader_helper' => $uploaderHelper, // Pour afficher l'image actuelle
        ]);
    }

    #[Route('/{id}', name: 'app_planche_delete', methods: ['POST'])]
    public function delete(Request $request, Planche $planche, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper): Response
    {
        $imageFilename = $planche->getImageFilename(); // Récupérer le nom du fichier avant la suppression

        if ($this->isCsrfTokenValid('delete'.$planche->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($planche);
            $entityManager->flush();

            // Supprimer le fichier image après la suppression de l'entité
            if ($imageFilename) {
                $uploaderHelper->removePlancheImage($imageFilename);
            }
            $this->addFlash('success', 'Planche supprimée avec succès.');
        }

        return $this->redirectToRoute('app_planche_index', [], Response::HTTP_SEE_OTHER);
    }
}
