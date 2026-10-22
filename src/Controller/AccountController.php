<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Form\AdresseFormType;
use App\Form\ChangePasswordFormType;
use App\Form\ProfilFormType;
use App\Repository\CommandeRepository;
use App\Service\InvoicePdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mon-compte')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $utilisateur = $this->utilisateur();

        return $this->render('account/index.html.twig', [
            'adresse' => $this->adressePrincipale($utilisateur),
            'nbCommandes' => $commandeRepository->count(['utilisateur' => $utilisateur]),
            'dernieresCommandes' => $commandeRepository->findBy(
                ['utilisateur' => $utilisateur],
                ['dateCreation' => 'DESC'],
                3
            ),
        ]);
    }

    #[Route('/commandes', name: 'app_account_orders', methods: ['GET'])]
    public function orders(CommandeRepository $commandeRepository): Response
    {
        return $this->render('account/orders.html.twig', [
            'commandes' => $commandeRepository->findBy(
                ['utilisateur' => $this->utilisateur()],
                ['dateCreation' => 'DESC']
            ),
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_account_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function orderShow(Commande $commande): Response
    {
        // Un client ne peut consulter que ses propres commandes.
        if ($commande->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('account/order_show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commandes/{id}/facture', name: 'app_account_order_invoice', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function orderInvoice(Commande $commande, InvoicePdfGenerator $generator): Response
    {
        if ($commande->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Pas de facture pour une commande annulée.
        if ($commande->getStatut() === Commande::STATUT_ANNULEE) {
            throw $this->createNotFoundException('Aucune facture pour une commande annulée.');
        }

        return new Response($generator->generer($commande), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $generator->numero($commande) . '.pdf'
            ),
        ]);
    }

    #[Route('/profil', name: 'app_account_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProfilFormType::class, $this->utilisateur());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/form.html.twig', [
            'titre' => 'Mes informations',
            'form' => $form,
        ]);
    }

    #[Route('/adresse', name: 'app_account_address', methods: ['GET', 'POST'])]
    public function address(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->utilisateur();
        $adresse = $this->adressePrincipale($utilisateur)
            ?? (new Adresse())->setUtilisateur($utilisateur);

        $form = $this->createForm(AdresseFormType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setParDefaut(true);
            $entityManager->persist($adresse);
            $entityManager->flush();
            $this->addFlash('success', 'Votre adresse a été enregistrée.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/form.html.twig', [
            'titre' => 'Mon adresse de livraison',
            'form' => $form,
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_account_password', methods: ['GET', 'POST'])]
    public function password(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $utilisateur = $this->utilisateur();

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur->setPassword(
                $passwordHasher->hashPassword($utilisateur, $form->get('newPassword')->getData())
            );
            $entityManager->flush();

            // Le mot de passe a changé : on reconnecte l'utilisateur pour que sa session reste valide.
            $security->login($utilisateur, 'form_login', 'main');

            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/form.html.twig', [
            'titre' => 'Changer mon mot de passe',
            'form' => $form,
        ]);
    }

    private function utilisateur(): Utilisateur
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        return $utilisateur;
    }

    /** Adresse par défaut du client, sinon la première, sinon null. */
    private function adressePrincipale(Utilisateur $utilisateur): ?Adresse
    {
        $premiere = null;
        foreach ($utilisateur->getAdresses() as $adresse) {
            if ($adresse->isParDefaut()) {
                return $adresse;
            }
            $premiere ??= $adresse;
        }

        return $premiere;
    }
}