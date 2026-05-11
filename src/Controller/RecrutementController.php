<?php
// src/Controller/RecrutementController.php
namespace App\Controller;

use App\Entity\Recrutement;
use App\Form\RecrutementType;
use App\Repository\RecrutementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recrutement')]
class RecrutementController extends AbstractController
{
    #[Route('/', name: 'recrutement_index')]
    public function index(RecrutementRepository $repo): Response
    {
        return $this->render('recrutement/index.html.twig', [
            'recrutements' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'recrutement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $recrutement = new Recrutement();
        $form = $this->createForm(RecrutementType::class, $recrutement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recrutement);
            $em->flush();
            $this->addFlash('success', 'Offre de recrutement créée !');
            return $this->redirectToRoute('recrutement_index');
        }

        return $this->render('recrutement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'recrutement_show')]
    public function show(Recrutement $recrutement): Response
    {
        return $this->render('recrutement/show.html.twig', [
            'recrutement' => $recrutement,
        ]);
    }

    #[Route('/{id}/edit', name: 'recrutement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recrutement $recrutement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RecrutementType::class, $recrutement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offre modifiée !');
            return $this->redirectToRoute('recrutement_index');
        }

        return $this->render('recrutement/edit.html.twig', [
            'form' => $form->createView(),
            'recrutement' => $recrutement,
        ]);
    }

    #[Route('/{id}/delete', name: 'recrutement_delete', methods: ['POST'])]
    public function delete(Request $request, Recrutement $recrutement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recrutement->getId(), $request->request->get('_token'))) {
            $em->remove($recrutement);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée !');
        }
        return $this->redirectToRoute('recrutement_index');
    }
}