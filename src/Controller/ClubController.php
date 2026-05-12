<?php

namespace App\Controller;

use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\ClubMember;

#[Route('/club')]
final class ClubController extends AbstractController
{
    #[Route(name: 'app_club_index', methods: ['GET'])]
    public function index(ClubRepository $repo): Response
{
    $clubs = $repo->findBy(['status' => 'active']);

    return $this->render('club/index.html.twig', [
        'clubs' => $clubs,
    ]);
}

    #[Route('/club/new', name: 'app_club_new')]

public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $club = new Club();
    $form = $this->createForm(ClubType::class, $club);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // HANDLE LOGO UPLOAD
        $logoFile = $form->get('logo')->getData();

        if ($logoFile) {
            $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

            try {
                $logoFile->move(
                    $this->getParameter('logos_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
            }

            $club->setLogo($newFilename);
        }

        $club->setStatus('pending');
        $club->setCreatedAt(new \DateTimeImmutable());
        $em->persist($club);
        $em->flush();

        $this->addFlash('success', 'Votre proposition a été envoyée. En attente de validation.');
        return $this->redirectToRoute('app_club_index');
    }

    return $this->render('club/new.html.twig', [
        'club' => $club,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_club_show', methods: ['GET'])]
    public function show(Club $club): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $club,
        ]);
    }



   #[Route('/{id}/edit', name: 'app_club_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Club $club, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ClubType::class, $club);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        
        $logoFile = $form->get('logo')->getData();

        if ($logoFile) {
            $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

            try {
                $logoFile->move(
                    $this->getParameter('logos_directory'),
                    $newFilename
                );
                
 
                $club->setLogo($newFilename); 
                
            } catch (FileException $e) {
                console.log('Error uploading file: ' . $e->getMessage());
            }
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('club/edit.html.twig', [
        'club' => $club,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_club_delete', methods: ['POST'])]
    public function delete(Request $request, Club $club, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$club->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($club);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/clubs/pending', name: 'app_club_pending')]
#[IsGranted('ROLE_ADMIN')]
public function pending(ClubRepository $repo): Response
{
    return $this->render('club/pending.html.twig', [
        'clubs' => $repo->findBy(['status' => 'pending']),
    ]);
}

#[Route('/admin/clubs/{id}/review', name: 'app_club_review', methods: ['POST'])]
#[IsGranted('ROLE_ADMIN')]
public function review(Club $club, Request $request, EntityManagerInterface $em): Response
{
    $action = $request->request->get('action');

  if ($action === 'approve') {
    $club->setStatus('active');

    
    $existing = $club->getClubMembers()->filter(
        fn($m) => $m->getUser() === $club->getProposedBy()
    );

    if ($existing->isEmpty()) {
        $member = new ClubMember();
        $member->setClub($club);
        $member->setUser($club->getProposedBy());
        $member->setRole('President');
        $member->setJoinedAt(new \DateTimeImmutable());

        $em->persist($member);
    }

    $this->addFlash('success', 'Club approuvé et président ajouté.');
} elseif ($action === 'reject') {
        $club->setStatus('rejected');
        $this->addFlash('warning', 'Club rejeté.');
    }

    $em->flush();

    return $this->redirectToRoute('app_club_pending');
}
}
