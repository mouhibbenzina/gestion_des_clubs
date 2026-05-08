<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PresidentController extends AbstractController
{
    #[Route('/president', name: 'app_president')]
    public function index(): Response
    {
        return $this->render('president/index.html.twig', [
            'controller_name' => 'PresidentController',
        ]);
    }
}
