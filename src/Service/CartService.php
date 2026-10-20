<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const SESSION_KEY = 'panier';
    private const QUANTITE_MAX = 99;

    public function __construct(
        private RequestStack $requestStack,
        private ProduitRepository $produitRepository,
    ) {
    }

    /** @return array<int, int> idProduit => quantité */
    private function getRaw(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    private function save(array $cart): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $cart);
    }

    public function add(int $produitId): void
    {
        $cart = $this->getRaw();
        $cart[$produitId] = min(($cart[$produitId] ?? 0) + 1, self::QUANTITE_MAX);
        $this->save($cart);
    }

    public function decrease(int $produitId): void
    {
        $cart = $this->getRaw();
        if (!isset($cart[$produitId])) {
            return;
        }
        if ($cart[$produitId] <= 1) {
            unset($cart[$produitId]);
        } else {
            --$cart[$produitId];
        }
        $this->save($cart);
    }

    public function remove(int $produitId): void
    {
        $cart = $this->getRaw();
        unset($cart[$produitId]);
        $this->save($cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    /** Nombre total d'articles (sans requête SQL : appelé sur chaque page). */
    public function getCount(): int
    {
        return array_sum($this->getRaw());
    }

    /**
     * @return list<array{produit: \App\Entity\Produit, quantite: int, sousTotal: string}>
     */
    public function getItems(): array
    {
        $cart = $this->getRaw();
        if ($cart === []) {
            return [];
        }

        $items = [];
        foreach ($this->produitRepository->findBy(['id' => array_keys($cart)]) as $produit) {
            $quantite = $cart[$produit->getId()];
            $items[] = [
                'produit' => $produit,
                'quantite' => $quantite,
                'sousTotal' => $this->formatCents($this->toCents($produit->getPrix()) * $quantite),
            ];
        }

        return $items;
    }

    public function getTotal(): string
    {
        $totalCents = 0;
        foreach ($this->getItems() as $item) {
            $totalCents += $this->toCents($item['produit']->getPrix()) * $item['quantite'];
        }

        return $this->formatCents($totalCents);
    }

    // Les calculs d'argent se font en centimes (entiers) pour éviter les erreurs d'arrondi.
    private function toCents(?string $prix): int
    {
        return (int) round(((float) $prix) * 100);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}