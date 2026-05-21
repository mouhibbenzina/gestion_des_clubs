<?php

namespace App\Controller;

use App\Entity\ClubMember;
use App\Entity\Evenement;
use App\Entity\Participation;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participation')]
final class ParticipationController extends AbstractController
{
    #[Route(name: 'app_participation_index', methods: ['GET'])]
    public function index(ParticipationRepository $participationRepository): Response
    {
        $user = $this->getUser();
        $participations = $user
            ? $participationRepository->findBy(['user' => $user], ['registeredAt' => 'DESC'])
            : [];

        return $this->render('participation/index.html.twig', [
            'participations' => $participations,
        ]);
    }

    #[Route('/new', name: 'app_participation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour participer.');
            return $this->redirectToRoute('app_login');
        }

        $eventId = $request->query->get('eventId');
        $evenement = $eventId ? $entityManager->getRepository(Evenement::class)->find($eventId) : null;

        if (!$evenement) {
            $this->addFlash('error', 'Événement introuvable.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $club = $evenement->getClub();
        if (!$club) {
            $this->addFlash('error', 'Cet événement n\'est associé à aucun club.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $isMember = $entityManager->getRepository(ClubMember::class)->findOneBy([
            'user' => $user,
            'club' => $club,
        ]);

        if (!$isMember) {
            $this->addFlash('error', 'Vous devez être membre du club organisateur pour participer à cet événement.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $existing = $entityManager->getRepository(Participation::class)->findOneBy([
            'user' => $user,
            'evenement' => $evenement,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        $participation = new Participation();
        $participation->setUser($user);
        $participation->setEvenement($evenement);

        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participation->setRegisteredAt(new \DateTimeImmutable());
            $participation->setPresenceStatus('inscrit');

            $entityManager->persist($participation);
            $entityManager->flush();

            $this->addFlash('success', 'Vous êtes inscrit à l\'événement !');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        return $this->render('participation/new.html.twig', [
            'participation' => $participation,
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participation_show', methods: ['GET'])]
    public function show(Participation $participation): Response
    {
        return $this->render('participation/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participation/edit.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participation_delete', methods: ['POST'])]
    public function delete(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/presence/{status}', name: 'app_participation_presence', methods: ['POST'])]
    public function markPresence(Participation $participation, string $status, EntityManagerInterface $entityManager): Response
    {
        $evenement = $participation->getEvenement();
        $user = $this->getUser();
        $isPresident = $user && $evenement->getClub() && $entityManager->getRepository(ClubMember::class)->findOneBy([
            'user' => $user,
            'club' => $evenement->getClub(),
            'role' => 'President',
        ]);
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$isPresident)) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        if (!in_array($status, ['present', 'absent'])) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_evenement_participants', ['id' => $evenement->getId()]);
        }

        $participation->setPresenceStatus($status);
        $entityManager->flush();

        $this->addFlash('success', 'Statut de présence mis à jour.');
        return $this->redirectToRoute('app_evenement_participants', ['id' => $evenement->getId()]);
    }

    #[Route('/cancel/{eventId}', name: 'app_participation_cancel', methods: ['POST'])]
    public function cancel(int $eventId, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $evenement = $entityManager->getRepository(Evenement::class)->find($eventId);
        if (!$evenement) {
            $this->addFlash('error', 'Événement introuvable.');
            return $this->redirectToRoute('app_evenement_index');
        }

        if ($evenement->getDateDebut() <= new \DateTime()) {
            $this->addFlash('error', 'Impossible d\'annuler : l\'événement a déjà commencé.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $eventId]);
        }

        $participation = $entityManager->getRepository(Participation::class)->findOneBy([
            'user' => $user,
            'evenement' => $evenement,
        ]);

        if (!$participation) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cet événement.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $eventId]);
        }

        $entityManager->remove($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Participation annulée.');
        return $this->redirectToRoute('app_evenement_show', ['id' => $eventId]);
    }
}
