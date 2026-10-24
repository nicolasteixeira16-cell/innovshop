<?php

namespace App\Tests;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FonctionnelTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    /** @param list<string> $roles */
    protected function creerUtilisateur(string $email, array $roles = []): Utilisateur
    {
        $utilisateur = (new Utilisateur())
            ->setEmail($email)
            ->setPrenom('Jean')
            ->setNom('Dupont')
            ->setPassword('mot-de-passe-factice')
            ->setRoles($roles);

        $this->em->persist($utilisateur);
        $this->em->flush();

        return $utilisateur;
    }

    protected function creerCommande(Utilisateur $proprietaire, string $statut = Commande::STATUT_EN_ATTENTE): Commande
    {
        $commande = (new Commande())
            ->setStatut($statut)
            ->setTotal('59.97')
            ->setUtilisateur($proprietaire)
            ->setAdresseLivraison('1 rue du Test, 75001 Paris, France');
        $this->em->persist($commande);

        $ligne = (new LigneCommande())
            ->setNomProduit('Casque Test')
            ->setPrixUnitaire('19.99')
            ->setQuantite(3)
            ->setCommande($commande);
        $this->em->persist($ligne);

        $this->em->flush();
        $this->em->clear();

        return $commande;
    }
}