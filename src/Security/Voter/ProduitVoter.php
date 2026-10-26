<?php

namespace App\Security\Voter;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Règles d'accès aux produits :
 * - un vendeur ne peut modifier ou supprimer que ses propres produits ;
 * - un prototype ne peut être commandé que par un testeur validé (ou un administrateur).
 */
class ProduitVoter extends Voter
{
    public const MODIFIER = 'PRODUIT_MODIFIER';
    public const COMMANDER = 'PRODUIT_COMMANDER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::MODIFIER, self::COMMANDER], true)
            && $subject instanceof Produit;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /* @var Produit $subject */
        if (self::COMMANDER === $attribute) {
            return $this->peutCommander($subject, $token);
        }

        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        return $subject->getVendeur()?->getId() === $utilisateur->getId();
    }

    private function peutCommander(Produit $produit, TokenInterface $token): bool
    {
        // Les produits "normaux" sont ouverts à tous
        if (!$produit->isPrototype()) {
            return true;
        }

        if (!$token->getUser() instanceof Utilisateur) {
            return false;
        }

        return [] !== array_intersect(['ROLE_TESTEUR', 'ROLE_ADMIN'], $token->getRoleNames());
    }
}
