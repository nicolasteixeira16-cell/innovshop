<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\DevenirVendeurFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DevenirVendeurController extends AbstractController
{
    #[Route('/devenir-vendeur', name: 'app_devenir_vendeur')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $form = $this->createForm(DevenirVendeurFormType::class);
        $form->handleRequest($request);

        $peutDemander = !$this->isGranted('ROLE_VENDEUR') && !$utilisateur->isDemandeVendeur();

        if ($peutDemander && $form->isSubmitted() && $form->isValid()) {
            $utilisateur->setNomBoutique($form->get('nomBoutique')->getData());
            $utilisateur->setDemandeVendeur(true);
            $em->flush();

            $this->addFlash('success', 'Votre demande a bien été envoyée. Un administrateur va l\'examiner.');

            return $this->redirectToRoute('app_devenir_vendeur');
        }

        return $this->render('vendeur/devenir.html.twig', [
            'form' => $form,
        ]);
    }
}
