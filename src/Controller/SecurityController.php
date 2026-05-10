<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        if ($this->isGranted('ROLE_ADMIN'))       return $this->redirectToRoute('admin_dashboard');
        if ($this->isGranted('ROLE_PRESIDENT'))   return $this->redirectToRoute('president_dashboard');
        if ($this->isGranted('ROLE_RESPONSABLE')) return $this->redirectToRoute('responsable_dashboard');
        return $this->redirectToRoute('etudiant_dashboard');
    }
}