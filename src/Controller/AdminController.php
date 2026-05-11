<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(UserRepository $repo): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'etudiants'   => $repo->findByRole('ROLE_USER'),
            'presidents'  => $repo->findByRole('ROLE_PRESIDENT'),
            'responsables'=> $repo->findByRole('ROLE_RESPONSABLE'),
            'admins'      => $repo->findByRole('ROLE_ADMIN'),
        ]);
    }

    #[Route('/user/new/{role}', name: 'user_new')]
    public function newUser(string $role, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setPassword($hasher->hashPassword($user, $request->request->get('password')));
            $user->setMatricule($request->request->get('matricule'));
            $user->setRoles([match($role) {
                'president'   => 'ROLE_PRESIDENT',
                'responsable' => 'ROLE_RESPONSABLE',
                'admin'       => 'ROLE_ADMIN',
                default       => 'ROLE_USER',
            }]);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/user_form.html.twig', ['role' => $role]);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_dashboard');
    }
}