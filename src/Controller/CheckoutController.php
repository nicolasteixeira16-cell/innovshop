<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Form\LivraisonFormType;
use App\Security\Voter\CommandeVoter;
use App\Security\Voter\ProduitVoter;
use App\Service\CartService;
use App\Service\CommandeMailer;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    private const SESSION_LIVRAISON = 'checkout_livraison';

    // Étape 1 : vérification du panier
    #[Route('/recapitulatif', name: 'app_checkout_recap', methods: ['GET'])]
    public function recap(CartService $cart): Response
    {
        if ($redirection = $this->verifierPanier($cart)) {
            return $redirection;
        }

        return $this->render('checkout/recap.html.twig', [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ]);
    }

    // Étape 2 : informations de livraison
    #[Route('/livraison', name: 'app_checkout_livraison', methods: ['GET', 'POST'])]
    public function livraison(Request $request, CartService $cart): Response
    {
        if ($redirection = $this->verifierPanier($cart)) {
            return $redirection;
        }

        $session = $request->getSession();
        $donnees = $session->get(self::SESSION_LIVRAISON) ?? $this->adressePreRemplie();

        $form = $this->createForm(LivraisonFormType::class, $donnees);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set(self::SESSION_LIVRAISON, $form->getData());

            return $this->redirectToRoute('app_checkout_confirmation');
        }

        return $this->render('checkout/livraison.html.twig', [
            'form' => $form,
        ]);
    }

    // Étape 3 : confirmation
    #[Route('/confirmation', name: 'app_checkout_confirmation', methods: ['GET'])]
    public function confirmation(Request $request, CartService $cart): Response
    {
        if ($redirection = $this->verifierPanier($cart)) {
            return $redirection;
        }

        $livraison = $request->getSession()->get(self::SESSION_LIVRAISON);
        if (!$livraison) {
            return $this->redirectToRoute('app_checkout_livraison');
        }

        return $this->render('checkout/confirmation.html.twig', [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
            'livraison' => $livraison,
        ]);
    }

    // Enregistrement de la commande
    #[Route('/valider', name: 'app_checkout_valider', methods: ['POST'])]
    public function valider(
        Request $request,
        CartService $cart,
        OrderService $orderService,
        CommandeMailer $commandeMailer,
    ): Response {
        if (!$this->isCsrfTokenValid('checkout', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($redirection = $this->redirigerSiPanierVide($cart)) {
            return $redirection;
        }

        $session = $request->getSession();
        $livraison = $session->get(self::SESSION_LIVRAISON);
        $utilisateur = $this->getUser();

        if (!$livraison) {
            return $this->redirectToRoute('app_checkout_livraison');
        }
        if (!$utilisateur instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        $commande = $orderService->creerDepuisPanier($utilisateur, $livraison);
        $session->remove(self::SESSION_LIVRAISON);

        if ($commandeMailer->envoyerConfirmation($commande)) {
            $this->addFlash('success', 'Un email de confirmation vous a été envoyé.');
        } else {
            $this->addFlash('warning', "Votre commande est enregistrée, mais l'email de confirmation n'a pas pu être envoyé.");
        }

        return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
    }

    #[Route('/merci/{id}', name: 'app_checkout_success', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function success(Commande $commande): Response
    {
        $this->denyAccessUnlessGranted(CommandeVoter::VOIR, $commande);

        return $this->render('checkout/success.html.twig', [
            'commande' => $commande,
        ]);
    }

    private function redirigerSiPanierVide(CartService $cart): ?Response
    {
        if (0 === $cart->getCount()) {
            $this->addFlash('warning', 'Votre panier est vide.');

            return $this->redirectToRoute('app_cart');
        }

        return null;
    }

    /** Panier vide, ou prototype réservé aux testeurs : retour au panier avec un message. */
    private function verifierPanier(CartService $cart): ?Response
    {
        if ($redirection = $this->redirigerSiPanierVide($cart)) {
            return $redirection;
        }

        foreach ($cart->getItems() as $item) {
            if (!$this->isGranted(ProduitVoter::COMMANDER, $item['produit'])) {
                $this->addFlash('warning', sprintf(
                    '« %s » est un prototype réservé aux testeurs validés. Retirez-le de votre panier pour continuer.',
                    $item['produit']->getNom(),
                ));

                return $this->redirectToRoute('app_cart');
            }
        }

        return null;
    }

    /** @return array{rue?: string, codePostal?: string, ville?: string, pays: string} */
    private function adressePreRemplie(): array
    {
        $utilisateur = $this->getUser();

        if ($utilisateur instanceof Utilisateur) {
            foreach ($utilisateur->getAdresses() as $adresse) {
                if ($adresse->isParDefaut()) {
                    return [
                        'rue' => $adresse->getRue(),
                        'codePostal' => $adresse->getCodePostal(),
                        'ville' => $adresse->getVille(),
                        'pays' => $adresse->getPays(),
                    ];
                }
            }
        }

        return ['pays' => 'France'];
    }
}
