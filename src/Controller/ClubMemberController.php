<?php

namespace App\Controller;

use App\Entity\ClubMember;
use App\Entity\User;
use App\Repository\ClubMemberRepository;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClubMemberController extends AbstractController
{
    #[Route('/club/{id}/join', name: 'app_club_join', methods: ['GET', 'POST'])]
    public function join(
        int $id,
        ClubRepository $clubRepo,
        ClubMemberRepository $memberRepo,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour rejoindre un club.');
            return $this->redirectToRoute('app_club_index');
        }

        $club = $clubRepo->find($id);
        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        // check already member
        $existing = $memberRepo->findOneBy([
            'user' => $user,
            'club' => $club,
        ]);
        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà membre de ce club.');
            return $this->redirectToRoute('app_club_index');
        }

        // handle form submission
        if ($request->isMethod('POST')) {
            $enteredCode = $request->request->get('code');

            if (!$club->getCode()) {
                $this->addFlash('error', 'Ce club n\'a pas encore de code d\'accès. Contactez l\'administrateur.');
                return $this->redirectToRoute('app_club_join', ['id' => $id]);
            }

            if ($enteredCode !== $club->getCode()) {
                $this->addFlash('error', 'Code incorrect. Veuillez réessayer.');
                return $this->redirectToRoute('app_club_join', ['id' => $id]);
            }

            $member = new ClubMember();
            $member->setUser($user);
            $member->setClub($club);
            $member->setRole('membre');
            $member->setJoinedAt(new \DateTimeImmutable());

            $em->persist($member);
            $em->flush();

            $this->addFlash('success', 'Bienvenue dans le club ' . $club->getName() . ' !');
            return $this->redirectToRoute('app_club_index');
        }

        return $this->render('club_member/join.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/club/{id}/leave', name: 'app_club_leave', methods: ['POST'])]
    public function leave(
        int $id,
        ClubRepository $clubRepo,
        ClubMemberRepository $memberRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour quitter un club.');
            return $this->redirectToRoute('app_club_index');
        }

        $club = $clubRepo->find($id);
        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        $member = $memberRepo->findOneBy([
            'user' => $user,
            'club' => $club,
        ]);

        if (!$member) {
            $this->addFlash('warning', 'Vous n\'êtes pas membre de ce club.');
            return $this->redirectToRoute('app_club_index');
        }

        $em->remove($member);
        $em->flush();

        $this->addFlash('success', 'Vous avez quitté le club ' . $club->getName() . '.');
        return $this->redirectToRoute('app_club_index');
    }
}