<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(CartService $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Produit $produit, Request $request, CartService $cart): Response
    {
        $this->verifierJeton($request);
        $cart->add($produit->getId());

        return $this->repondre($request, $cart, sprintf('« %s » a été ajouté au panier.', $produit->getNom()));
    }

    #[Route('/diminuer/{id}', name: 'app_cart_decrease', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function decrease(Produit $produit, Request $request, CartService $cart): Response
    {
        $this->verifierJeton($request);
        $cart->decrease($produit->getId());

        return $this->repondre($request, $cart, 'Quantité mise à jour.');
    }

    #[Route('/retirer/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(Produit $produit, Request $request, CartService $cart): Response
    {
        $this->verifierJeton($request);
        $cart->remove($produit->getId());

        return $this->repondre($request, $cart, sprintf('« %s » a été retiré du panier.', $produit->getNom()));
    }

    #[Route('/vider', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request, CartService $cart): Response
    {
        $this->verifierJeton($request);
        $cart->clear();

        return $this->repondre($request, $cart, 'Votre panier a été vidé.');
    }

    /** Réponse JSON pour l'AJAX, redirection classique sinon. */
    private function repondre(Request $request, CartService $cart, string $message): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->json(['count' => $cart->getCount(), 'message' => $message]);
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_cart');
    }

    private function verifierJeton(Request $request): void
    {
        if (!$this->isCsrfTokenValid('cart', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }
}
