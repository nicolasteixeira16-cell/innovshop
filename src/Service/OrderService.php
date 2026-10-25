<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartService $cartService,
    ) {
    }

    /**
     * @param array{rue: string, codePostal: string, ville: string, pays: string} $livraison
     */
    public function creerDepuisPanier(Utilisateur $utilisateur, array $livraison): Commande
    {
        $items = $this->cartService->getItems();
        if ([] === $items) {
            throw new \LogicException('Impossible de commander : le panier est vide.');
        }

        $commande = new Commande();
        $commande
            ->setUtilisateur($utilisateur)
            ->setStatut(Commande::STATUT_EN_ATTENTE)
            ->setTotal($this->cartService->getTotal())
            ->setAdresseLivraison($this->formaterAdresse($utilisateur, $livraison));

        // Tout est enregistré dans une transaction : la commande et ses lignes, ou rien.
        $this->entityManager->wrapInTransaction(function () use ($commande, $items): void {
            $this->entityManager->persist($commande);

            foreach ($items as $item) {
                $produit = $item['produit'];

                $ligne = new LigneCommande();
                $ligne
                    ->setNomProduit($produit->getNom())
                    ->setPrixUnitaire($produit->getPrix())
                    ->setQuantite($item['quantite'])
                    ->setProduit($produit);

                $commande->addLigneCommande($ligne);
                $this->entityManager->persist($ligne);
            }
        });

        $this->cartService->clear();

        return $commande;
    }

    private function formaterAdresse(Utilisateur $utilisateur, array $livraison): string
    {
        return implode("\n", [
            trim($utilisateur->getPrenom().' '.$utilisateur->getNom()),
            $livraison['rue'],
            $livraison['codePostal'].' '.$livraison['ville'],
            $livraison['pays'],
        ]);
    }
}
