<?php

namespace App\Controller\Admin;

use App\Entity\Ecole;
use App\Form\EcoleType;
use App\Repository\EcoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ecole')]
class EcoleController extends AbstractController
{
    #[Route('/', name: 'admin_ecole_index', methods: ['GET'])]
    public function index(EcoleRepository $ecoleRepository): Response
    {
        $ecoles = $ecoleRepository->findAllOrderedByName();
        return $this->render('admin/ecole/index.html.twig', [
            'ecoles' => $ecoles,
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
            return $this->redirectToRoute('admin_ecole_index');
        }

        return $this->render('admin/ecole/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_show', methods: ['GET'])]
    public function show(Ecole $ecole): Response
    {
        // La méthode findByEcole n'est pas nécessaire ici car Doctrine charge 
        // automatiquement les prisesDeVue grâce à la relation OneToMany

        return $this->render('admin/ecole/show.html.twig', [
            'ecole' => $ecole,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ecole_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Ecole $ecole, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EcoleType::class, $ecole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_ecole_index');
        }

        return $this->render('admin/ecole/edit.html.twig', [
            'ecole' => $ecole,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_ecole_delete', methods: ['POST'])]
    public function delete(Request $request, Ecole $ecole, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ecole->getId(), $request->request->get('_token'))) {
            $em->remove($ecole);
            $em->flush();
        }
        return $this->redirectToRoute('admin_ecole_index');
    }
}