<?php

namespace App\Controller\Admin;

use App\Entity\Ecole;
use App\Form\EcoleType;
use App\Repository\EcoleRepository;
use App\Security\Voter\EcoleVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ecole')]
#[IsGranted('ROLE_ADMIN')]
class EcoleController extends AbstractController
{
    #[Route('/', name: 'admin_ecole_index', methods: ['GET'])]
    public function index(EcoleRepository $ecoleRepository): Response
    {
        return $this->render('admin/ecole/index.html.twig', [
            'ecoles' => $ecoleRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/new', name: 'admin_ecole_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ecole = new Ecole();
        $form = $this->createForm(EcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ecole);
            $em->flush();
            
            $this->addFlash('success', 'L\'école a été créée avec succès.');
            return $this->redirectToRoute('admin_ecole_index');
        }

        return $this->render('admin/ecole/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_show', methods: ['GET'])]
    #[IsGranted(EcoleVoter::ECOLE_VIEW, subject: 'ecole')]
    public function show(Ecole $ecole): Response
    {
        return $this->render('admin/ecole/show.html.twig', [
            'ecole' => $ecole,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ecole_edit', methods: ['GET','POST'])]
    #[IsGranted(EcoleVoter::ECOLE_EDIT, subject: 'ecole')]
    public function edit(Request $request, Ecole $ecole, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'L\'école a été modifiée avec succès.');
            return $this->redirectToRoute('admin_ecole_index');
        }

        return $this->render('admin/ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_delete', methods: ['POST'])]
    #[IsGranted(EcoleVoter::ECOLE_DELETE, subject: 'ecole')]
    public function delete(Request $request, Ecole $ecole, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ecole->getId(), $request->request->get('_token'))) {
            $em->remove($ecole);
            $em->flush();
            
            $this->addFlash('success', 'L\'école a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_ecole_index');
    }
}