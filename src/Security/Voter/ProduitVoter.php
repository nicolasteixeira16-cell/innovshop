<?php

namespace App\Security\Voter;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Un vendeur ne peut modifier ou supprimer que ses propres produits.
 */
class ProduitVoter extends Voter
{
    public const MODIFIER = 'PRODUIT_MODIFIER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MODIFIER === $attribute && $subject instanceof Produit;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        /* @var Produit $subject */
        return $subject->getVendeur()?->getId() === $utilisateur->getId();
    }
}
