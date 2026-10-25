<?php

namespace App\Security\Voter;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Règle d'accès aux commandes : un client ne peut consulter
 * que ses propres commandes (détail et facture).
 */
final class CommandeVoter extends Voter
{
    public const VOIR = 'COMMANDE_VOIR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VOIR === $attribute && $subject instanceof Commande;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        // Visiteur non connecté : accès refusé.
        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        return $subject->getUtilisateur()?->getId() === $utilisateur->getId();
    }
}
