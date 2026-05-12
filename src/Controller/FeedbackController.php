<?php
// src/Controller/FeedbackController.php
namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'feedback_index')]
    public function index(FeedbackRepository $repo): Response
    {
        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $repo->findAll(),
        ]);
    }

    #[Route('/new/{evenementId}', name: 'feedback_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, int $evenementId): Response
    {
        $evenement = $em->getRepository(\App\Entity\Evenement::class)->find($evenementId);
        $feedback = new Feedback();
        $feedback->setEvenement($evenement);
        $feedback->setUser($this->getUser());

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();
            $this->addFlash('success', 'Feedback envoyé !');
            return $this->redirectToRoute('feedback_index');
        }

        return $this->render('feedback/new.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/delete', name: 'feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feedback->getId(), $request->request->get('_token'))) {
            $em->remove($feedback);
            $em->flush();
        }
        return $this->redirectToRoute('feedback_index');
    }
}