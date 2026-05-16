<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\ClubMember;
use App\Entity\Evenement;
use App\Entity\Participation;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository, EntityManagerInterface $entityManager): Response
    {
        $qb = $evenementRepository->createQueryBuilder('e')
            ->andWhere('e.status IS NULL OR e.status NOT IN (:excluded)')
            ->setParameter('excluded', ['pending', 'rejected']);

        if ($clubId = $request->query->get('club')) {
            $qb->andWhere('e.club = :club')->setParameter('club', $clubId);
        }

        if ($dateFilter = $request->query->get('date')) {
            $now = new \DateTime();
            match ($dateFilter) {
                'upcoming' => $qb->andWhere('e.dateDebut >= :now')->setParameter('now', $now),
                'past' => $qb->andWhere('e.dateFin < :now')->setParameter('now', $now),
                'today' => $qb->andWhere('e.dateDebut >= :todayStart AND e.dateDebut < :todayEnd')
                    ->setParameter('todayStart', (new \DateTime())->setTime(0, 0))
                    ->setParameter('todayEnd', (new \DateTime())->setTime(23, 59, 59)),
                default => null,
            };
        }

        $qb->orderBy('e.dateDebut', 'DESC');

        $evenements = $qb->getQuery()->getResult();

        $now = new \DateTime();
        $upcomingCount = array_filter($evenements, fn($e) => $e->getDateDebut() >= $now);
        $totalParticipants = $entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Participation::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        $clubs = $entityManager->getRepository(Club::class)->findBy([], ['name' => 'ASC']);

        $userParticipations = [];
        if ($this->getUser()) {
            $parts = $entityManager->getRepository(Participation::class)->findBy(['user' => $this->getUser()]);
            foreach ($parts as $p) {
                $userParticipations[] = $p->getEvenement()->getId();
            }
        }

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'upcoming_count' => count($upcomingCount),
            'total_participants' => $totalParticipants,
            'clubs' => $clubs,
            'userParticipations' => $userParticipations,
        ]);
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $clubs = $this->getAccessibleClubs($user, $entityManager);
        if (empty($clubs)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour créer un événement.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement, [
            'club_choices' => $clubs,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedClub = $evenement->getClub();
            if (!in_array($selectedClub, $clubs)) {
                $this->addFlash('error', 'Club non autorisé.');
                return $this->redirectToRoute('app_evenement_index');
            }

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('evenements_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }

                $evenement->setImage($newFilename);
            }

            $evenement->setStatus('pending');
            $evenement->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'Événement proposé. En attente de validation.');
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/participants', name: 'app_evenement_participants', methods: ['GET'])]
    public function participants(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $evenement->getClub(), $entityManager))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_evenement_show', ['id' => $evenement->getId()]);
        }

        $participations = $entityManager->getRepository(Participation::class)->findBy(
            ['evenement' => $evenement],
            ['registeredAt' => 'ASC']
        );

        return $this->render('evenement/participants.html.twig', [
            'evenement' => $evenement,
            'participations' => $participations,
        ]);
    }

    #[Route('/admin/evenements/pending', name: 'app_evenement_pending')]
    public function pending(EvenementRepository $repo): Response
    {
        return $this->render('evenement/pending.html.twig', [
            'evenements' => $repo->findBy(['status' => 'pending']),
        ]);
    }

    #[Route('/admin/evenements/{id}/review', name: 'app_evenement_review', methods: ['POST'])]
    public function review(Evenement $evenement, Request $request, EntityManagerInterface $em): Response
    {
        $action = $request->request->get('action');

        if ($action === 'approve') {
            $evenement->setStatus('active');
            $this->addFlash('success', 'Événement approuvé.');
        } elseif ($action === 'reject') {
            $evenement->setStatus('rejected');
            $this->addFlash('warning', 'Événement rejeté.');
        }

        $em->flush();
        return $this->redirectToRoute('app_evenement_pending');
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $feedbacks = $evenement->getFeedbacks();
        $userParticipation = null;
        if ($this->getUser()) {
            $userParticipation = $entityManager->getRepository(Participation::class)->findOneBy([
                'user' => $this->getUser(),
                'evenement' => $evenement,
            ]);
        }

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'feedbacks' => $feedbacks,
            'userParticipation' => $userParticipation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $evenement->getClub(), $entityManager))) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour modifier cet événement.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $clubs = $this->getAccessibleClubs($user, $entityManager);
        $form = $this->createForm(EvenementType::class, $evenement, [
            'club_choices' => $clubs,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedClub = $evenement->getClub();
            if (!in_array($selectedClub, $clubs)) {
                $this->addFlash('error', 'Club non autorisé.');
                return $this->redirectToRoute('app_evenement_index');
            }

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('evenements_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }

                $evenement->setImage($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $evenement->getClub(), $entityManager))) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cet événement.');
            return $this->redirectToRoute('app_evenement_index');
        }

        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    private function isClubPresident($user, ?Club $club, EntityManagerInterface $em): bool
    {
        if (!$club) return false;
        $member = $em->getRepository(ClubMember::class)->findOneBy([
            'user' => $user,
            'club' => $club,
            'role' => 'President',
        ]);
        return $member !== null;
    }

    private function getAccessibleClubs($user, EntityManagerInterface $em): array
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $em->getRepository(Club::class)->findBy(['status' => 'active']);
        }

        $memberships = $em->getRepository(ClubMember::class)->findBy([
            'user' => $user,
            'role' => 'President',
        ]);

        return array_map(fn($m) => $m->getClub(), $memberships);
    }
}
