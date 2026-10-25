<?php

namespace App\Controller;

use App\Entity\CandidatureTesteur;
use App\Form\CandidatureTesteurFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TesteurController extends AbstractController
{
    #[Route('/devenez-testeurs', name: 'app_testeur')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $candidature = new CandidatureTesteur();
        $form = $this->createForm(CandidatureTesteurFormType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($candidature);
            $em->flush();

            $this->addFlash('success', 'Merci ! Votre candidature a bien été enregistrée, nous reviendrons vers vous.');

            return $this->redirectToRoute('app_testeur');
        }

        return $this->render('testeur/index.html.twig', [
            'form' => $form,
        ]);
    }
}