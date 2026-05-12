<?php

namespace App\Controller;

use App\Repository\ClubRepository;
use App\Repository\EvenementRepository;
use App\Repository\RecrutementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ClubRepository $clubRepo,
        EvenementRepository $eventRepo,
        UserRepository $userRepo,
        RecrutementRepository $recrutementRepo,
    ): Response {
        $activeClubs = $clubRepo->findBy(['status' => 'active']);
        $upcomingEvents = $eventRepo->findBy([], ['dateDebut' => 'ASC']);
        $openRecruitments = $recrutementRepo->findBy(['status' => 'ouverte']);
        $totalUsers = count($userRepo->findAll());

        return $this->render('home/index.html.twig', [
            'activeClubs' => $activeClubs,
            'upcomingEvents' => $upcomingEvents,
            'openRecruitments' => $openRecruitments,
            'totalUsers' => $totalUsers,
            'clubCount' => count($activeClubs),
            'eventCount' => count($upcomingEvents),
            'recruitmentCount' => count($openRecruitments),
        ]);
    }
}
