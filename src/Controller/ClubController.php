<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\ClubMember;
use App\Entity\Recrutement;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/club')]
final class ClubController extends AbstractController
{
    #[Route(name: 'app_club_index', methods: ['GET'])]
    public function index(Request $request, ClubRepository $repo, EntityManagerInterface $entityManager): Response
{
    $qb = $repo->createQueryBuilder('c')
        ->andWhere('c.status = :active')
        ->setParameter('active', 'active');

    if ($q = $request->query->get('q')) {
        $qb->andWhere('c.name LIKE :q OR c.description LIKE :q2')
            ->setParameter('q', "%$q%")
            ->setParameter('q2', "%$q%");
    }

    if ($domain = $request->query->get('domain')) {
        $qb->andWhere('c.domain = :domain')->setParameter('domain', $domain);
    }

    $qb->orderBy('c.name', 'ASC');

    $domains = $entityManager->createQueryBuilder()
        ->select('DISTINCT c2.domain')
        ->from(Club::class, 'c2')
        ->where('c2.status = :active')
        ->setParameter('active', 'active')
        ->orderBy('c2.domain', 'ASC')
        ->getQuery()
        ->getSingleColumnResult();

    return $this->render('club/index.html.twig', [
        'clubs' => $qb->getQuery()->getResult(),
        'domains' => $domains,
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

        $club->setProposedBy($this->getUser());
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
    $user = $this->getUser();
    if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $entityManager))) {
        $this->addFlash('error', 'Vous n\'avez pas les droits pour modifier ce club.');
        return $this->redirectToRoute('app_club_index');
    }

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
                $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
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

    #[Route('/{id}/manage', name: 'app_club_manage', methods: ['GET'])]
    public function manage(Club $club, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $em))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
        }

        $candidatures = $em->createQueryBuilder()
            ->select('c')
            ->from(\App\Entity\Candidature::class, 'c')
            ->join('c.recrutement', 'r')
            ->where('r.club = :club')
            ->orderBy('c.submittedAt', 'DESC')
            ->setParameter('club', $club)
            ->getQuery()
            ->getResult();

        return $this->render('club/manage.html.twig', [
            'club' => $club,
            'members' => $club->getClubMembers(),
            'recrutements' => $club->getRecrutements(),
            'candidatures' => $candidatures,
        ]);
    }

    #[Route('/{id}/manage/recrutement', name: 'app_club_recrutement_new', methods: ['POST'])]
    public function newRecrutement(Request $request, Club $club, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $em))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
        }

        $recrutement = new Recrutement();
        $recrutement->setClub($club);
        $recrutement->setTitle($request->request->get('title'));
        $recrutement->setDescription($request->request->get('description'));
        $recrutement->setRequirements($request->request->get('requirements'));
        $recrutement->setStatus('open');

        $deadline = $request->request->get('deadline');
        if ($deadline) {
            $recrutement->setDeadline(new \DateTime($deadline));
        }

        $em->persist($recrutement);
        $em->flush();

        $this->addFlash('success', 'Offre de recrutement créée.');
        return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
    }

    #[Route('/recrutement/{id}/toggle', name: 'app_recrutement_toggle', methods: ['POST'])]
    public function toggleRecrutement(Recrutement $recrutement, EntityManagerInterface $em): Response
    {
        $club = $recrutement->getClub();
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $em))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
        }

        $recrutement->setStatus($recrutement->getStatus() === 'open' ? 'closed' : 'open');
        $em->flush();

        $this->addFlash('success', 'Statut du recrutement mis à jour.');
        return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
    }

    #[Route('/member/{id}/role/{role}', name: 'app_club_member_role', methods: ['POST'])]
    public function changeMemberRole(ClubMember $member, string $role, EntityManagerInterface $em): Response
    {
        $club = $member->getClub();
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $em))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
        }

        if (!in_array($role, ['President', 'membre'])) {
            $this->addFlash('error', 'Rôle invalide.');
            return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
        }

        $member->setRole($role);
        $em->flush();

        $this->addFlash('success', 'Rôle mis à jour.');
        return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
    }

    #[Route('/member/{id}/remove', name: 'app_club_member_remove', methods: ['POST'])]
    public function removeMember(ClubMember $member, EntityManagerInterface $em): Response
    {
        $club = $member->getClub();
        $user = $this->getUser();
        if (!$user || (!$this->isGranted('ROLE_ADMIN') && !$this->isClubPresident($user, $club, $em))) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('app_club_show', ['id' => $club->getId()]);
        }

        if ($member->getRole() === 'President') {
            $this->addFlash('error', 'Impossible de retirer le président.');
            return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
        }

        $em->remove($member);
        $em->flush();

        $this->addFlash('success', 'Membre retiré du club.');
        return $this->redirectToRoute('app_club_manage', ['id' => $club->getId()]);
    }

private function isClubPresident($user, Club $club, EntityManagerInterface $em): bool
{
    $member = $em->getRepository(ClubMember::class)->findOneBy([
        'user' => $user,
        'club' => $club,
        'role' => 'President',
    ]);
    return $member !== null;
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
