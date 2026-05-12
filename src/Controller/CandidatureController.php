<?php
// src/Controller/CandidatureController.php
namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/candidature')]
class CandidatureController extends AbstractController
{
    #[Route('/', name: 'candidature_index')]
    public function index(CandidatureRepository $repo): Response
    {
        return $this->render('candidature/index.html.twig', [
            'candidatures' => $repo->findAll(),
        ]);
    }

    #[Route('/new/{recrutementId}', name: 'candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, int $recrutementId): Response
    {
        $recrutement = $em->getRepository(\App\Entity\Recrutement::class)->find($recrutementId);
        $candidature = new Candidature();
        $candidature->setRecrutement($recrutement);
        $candidature->setUser($this->getUser());

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle CV file upload
            $cvFile = $form->get('cvFile')->getData();
            if ($cvFile) {
                $newFilename = uniqid().'.'.$cvFile->guessExtension();
                $cvFile->move($this->getParameter('cv_directory'), $newFilename);
                $candidature->setCvFilename($newFilename);
            }

            $em->persist($candidature);
            $em->flush();
            $this->addFlash('success', 'Candidature soumise !');
            return $this->redirectToRoute('recrutement_index');
        }

        return $this->render('candidature/new.html.twig', [
            'form' => $form->createView(),
            'recrutement' => $recrutement,
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'candidature_status', methods: ['POST'])]
    public function updateStatus(Candidature $candidature, string $status, EntityManagerInterface $em): Response
    {
        $candidature->setStatus($status);
        $em->flush();
        $this->addFlash('success', 'Statut mis à jour !');
        return $this->redirectToRoute('candidature_index');
    }

    #[Route('/{id}/delete', name: 'candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getId(), $request->request->get('_token'))) {
            $em->remove($candidature);
            $em->flush();
        }
        return $this->redirectToRoute('candidature_index');
    }
}